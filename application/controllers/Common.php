<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Common extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('upload_files_model');

        //s3
        $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_default_bucket')], 'aws_s3_library_public');
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            header("HTTP/1.1 401 Unauthorized");
            return;
        }
    }

    /*
    *
    * https://www.tiny.cloud/docs/configure/file-image-upload/
    * https://www.tiny.cloud/docs/advanced/php-upload-handler/
    *
     */
    public function ajax_tinymce_file_save()
    {
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        $this->validate_access();

        $urlParts = parse_url($this->config->item('base_url'));
        $baseUrl = $urlParts['scheme'] . "://" . $urlParts['host'];
        $accepted_origins = [$baseUrl];

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // same-origin requests won't set an origin. If the origin is set, it must be valid.
            if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
                header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            } else {
                header("HTTP/1.1 403 Origin Denied");
                return;
            }
        }

        // Don't attempt to process the upload on an OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            header("Access-Control-Allow-Methods: POST, OPTIONS");
            return;
        }

        reset($_FILES);
        $temp = current($_FILES);

        if (is_uploaded_file($temp['tmp_name'])) {
            /*
              If your script needs to receive cookies, set images_upload_credentials : true in
              the configuration and enable the following two headers.
            */
            // header('Access-Control-Allow-Credentials: true');
            // header('P3P: CP="There is no P3P policy."');

            // Sanitize input
            if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
                header("HTTP/1.1 400 Invalid file name");
                return;
            }

            // Verify extension
            if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), ["gif", "jpg", "png"])) {
                header("HTTP/1.1 400 Invalid extension");
                return;
            }

            //check file size
            if (filesize($temp['tmp_name']) > 20000000) {
                header("HTTP/1.1 413 File too large. Make sure the file is not more than 20MB");
                return;
            }

            //upload file
            $sub_dir = 'uploads/tinymce/' . date("Y/m") . '/';
            $file_dir = FCPATH . $sub_dir;
            if (!file_exists($file_dir)) {
                $oldumask = umask(0);
                mkdir($file_dir, 0775, true);
                umask($oldumask);

                if (!file_exists($file_dir)) {
                    header("HTTP/1.1 500 Internal Server Error");
                    return;
                }
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $temp['tmp_name']);
            finfo_close($finfo);

            $filename = strtoupper(random_string('alnum', 8)) . date("His") . "_" . $this->security->sanitize_filename($temp['name']);
            if (!move_uploaded_file($temp['tmp_name'], $file_dir . $filename) || !file_exists($file_dir . $filename)) {
                header("HTTP/1.1 500 Internal Server Error");
                return;
            }

            //SAVE FILE TO S3
            if (ENVIRONMENT == "production") {
                //wait for object to be created in s3
                $file_url = $this->aws_s3_library_public->put_object($file_dir . $filename, $sub_dir . $filename, '', true);

                if (file_exists($file_dir . $filename)) {
                    unlink($file_dir . $filename);
                }
                
                if ($file_url === false) {
                    header("HTTP/1.1 500 Internal Server Error. Failed to upload in S3");
                    return;
                }
            } else {
                $file_url = base_url() . $sub_dir . $filename;
            }

            $this->db->trans_begin();

            $data = [];
            $data['url'] = $file_url;
            $data['filename'] = $temp['name'];

            if (!$this->upload_files_model->save($data)) {
                $this->db->trans_rollback();
                header("HTTP/1.1 500 Internal Server Error. " . ERROR_512);
                return;
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                header("HTTP/1.1 500 Internal Server Error. " . ERROR_512);
                return;
            }

            $this->db->trans_commit();

            // Respond to the successful upload with JSON.
            // Use a location key to specify the path to the saved image resource.
            // { location : '/your/uploaded/image/file'}
            echo json_encode(['location' => $file_url]);
        } else {
            // Notify editor that the upload failed
            header("HTTP/1.1 500 Server Error");
        }
    }
}
