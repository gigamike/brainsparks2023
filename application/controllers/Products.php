<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('products_model');

        //s3
        $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_default_bucket')], 'aws_s3_library_public');

        //initiate directories
        $this->relative_dir = 'uploads/' . date("Y/m") . '/';
        $this->absolute_dir = FCPATH . $this->relative_dir;

        if (!file_exists($this->absolute_dir)) {
            $oldumask = umask(0);
            mkdir($this->absolute_dir, 0775, true);
            umask($oldumask);

            if (!file_exists($this->absolute_dir)) {
                $this->email_library->notify_system_failure("Backend Systems failed to create the directory " . $this->absolute_dir);
                exit(EXIT_ERROR);
            }
        }
    }

    protected function validate_access($show_when_disabled = false)
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        return true;
    }

    public function index()
    {
        if (!$this->validate_access()) {
            return;
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "products";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */

        $view_data['styles'] = [
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/products/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'products';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        // Overview defaults
        $view_data['saved_filter'] = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('products/index', $view_data);
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_products()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dt_search_columns = [
            "u_code" => "tbl_products.u_code",
            "name" => "tbl_products.name",
        ];

        $dt_params = $this->input->get();

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        $condition = "";

        if (isset($dt_params['searchText']) && $dt_params['searchText'] != "") {
            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $dt_search_columns[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                //remove last ' OR '
                $str_search = substr($str_search, 0, strlen($str_search) - 4);
                $condition .= $condition == "" ? "WHERE (" . $str_search . ")" : " AND (" . $str_search . ")";
            }
        }

        $dataset = $this->products_model->dt_get_products($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->products_model->dt_get_products_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = "webform_" . $subset['u_code'];
            $dt_subset['DT_RowClass'] = "webform-row";

            $dt_subset['photo'] = "<img width='100px' src='" . $subset['photo'] . "'>";

            $dt_subset['actions'] = $this->load->view('products/section_dt_actions', $subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function add()
    {
        if (!$this->validate_access()) {
            return;
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "products";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/products/product.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'product_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];
        
        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('products/add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function update($id = null)
    {
        if (!$this->validate_access()) {
            return;
        }

        $product = $this->products_model->getById($id);
        if (!$product) {
            redirect(base_url() . "products", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "products";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/products/products.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'product_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['product'] = $product;

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('products/update', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        
        $name = isset($dataset['name']) ? trim($dataset['name']) : '';
        $price = isset($dataset['price']) ? trim($dataset['price']) : '';
        
        if ($name == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Name."]);
            return;
        }

        if ($price == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Price."]);
            return;
        }

        $allowedMediaFileTypes = [
            // Images
            'image/jpg',
            'image/jpeg',
            'image/png',
            'image/gif',
        ];

        if (isset($dataset['product_id']) && !empty($dataset['product_id'])) {
            //UPDATE
            $exitingProduct = $this->products_model->getById($dataset['product_id']);
            if (!$exitingCustomer) {
                echo json_encode(['successful' => false, 'error' => "Customer does not exists."]);
                return;
            }

            //START
            $this->db->trans_begin();

            $data = [];
            $data['id'] = $exitingCustomer->id;
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = $mobile_phone;

            $product_id = $this->products_model->save($data);
            if (!$product_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $this->db->trans_commit();

            echo json_encode(['successful' => true]);
        } else {
            //ADD NEW
            if (isset($_FILES['photo']) && !empty($_FILES['photo'])) {
                if (!isset($_FILES['photo']['tmp_name']) || !file_exists($_FILES['photo']['tmp_name'])) {
                    echo json_encode(['successful' => false, 'error' => "Error uploading file. Try again."]);
                    return;
                }

                //check file size
                if (filesize($_FILES['photo']['tmp_name']) > 20000000) {
                    echo json_encode(['successful' => false, 'error' => "File too large. Make sure the file is not more than 20MB."]);
                    return;
                }

                //check file type
                if (!in_array(strtolower(mime_content_type($_FILES['photo']['tmp_name'])), $allowedMediaFileTypes)) {
                    echo json_encode(['successful' => false, 'error' => "Invalid file type. Accepted types are " . implode(', ', $allowedMediaFileTypes)]);
                    return;
                }

                $image_info = getimagesize($_FILES['photo']['tmp_name']);
                $image_width = $image_info[0];
                $image_height = $image_info[1];

                /*
                if($image_width!=1024 && $image_height=576){
                    echo json_encode(['status' => 'failed', 'error' => "Invalid File Image dimension. Dimension should be 1024x576"]);
                    return;
                }
                */
            } else {
                echo json_encode(['status' => 'failed', 'error' => 'Required Field: Offer Type']);
                return;
            }
           
            //START
            $this->db->trans_begin();

            $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_default_bucket')], 'aws_s3_library_public');

            $data = [];
            $data['name'] = $name;
            $data['price'] = $price;

            $product_id = $this->products_model->save($data);
            if (!$product_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            //upload file
            $sub_dir = 'uploads/products/';
            $file_dir = FCPATH . $sub_dir;
            if (!file_exists($file_dir)) {
                $oldumask = umask(0);
                mkdir($file_dir, 0775, true);
                umask($oldumask);

                if (!file_exists($file_dir)) {
                    echo json_encode(['status' => 'failed', 'error' => "Internal Error. Error uploading file. Try again."]);
                    return;
                }
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
            $array = explode('.', $_FILES['photo']['name']);
            $extension = end($array);

            if(!in_array(trim(strtolower($extension)), ['jpg','jpeg','png','gif'])){
                echo json_encode(['status' => 'failed', 'error' => "Invalid File Type"]);
                return;
            }

            $filename = getRandomAlphaNum().".". pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);//$photo_id . "." . $extension;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $file_dir . $filename) || !file_exists($file_dir . $filename)) {
                echo json_encode(['status' => 'failed', 'error' => "Error uploading file. Try again."]);
                return;
            }

            //SAVE FILE TO S3
            if (ENVIRONMENT == "production") {
                //wait for object to be created in s3
                $file_url = $this->aws_s3_library_public->put_object($file_dir . $filename, $sub_dir . $filename, '', true);

                if ($file_url === false) {
                    echo json_encode(['status' => 'failed', 'error' => "Error uploading file. Try again."]);
                    return;
                }
            } else {
                $file_url = base_url() . $sub_dir . $filename;
            }

            $data = [
                'id' => $product_id,
                'photo' => $file_url,
            ];
            $product_id = $this->products_model->save($data);
            if (!$product_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $this->db->trans_commit();

            echo json_encode(['successful' => true]);
        }
    }
}
