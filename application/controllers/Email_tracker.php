<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email_tracker extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('workflow_builder_log_email_model');
        $this->load->model('workflow_builder_log_email_tracks_model');

        $this->load->library('user_agent');
    }

    public function workflow_builder()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: image/png');
        $image = FCPATH . "assets/img/system-logo.png";
        if (file_exists($image)) {
            echo file_get_contents($image);
        }

        $email_id = $this->input->get('id');
        if (!empty($email_id)) {
            $email_id = $this->encryption->url_decrypt($email_id);
            $email = $this->workflow_builder_log_email_model->getById($email_id);

            if ($email) {
                $this->db->trans_begin();

                $filter = [
                    'email_id' => $email->id,
                ];
                $fields = [
                    'id',
                ];
                $email_tracks = $this->workflow_builder_log_email_tracks_model->fetch($filter, [], 1, null, $fields);
                if (count($email_tracks) <= 0) {
                    if ($this->agent->is_browser()) {
                        $agent = $this->agent->browser() . ' ' . $this->agent->version();
                    } elseif ($this->agent->is_robot()) {
                        $agent = $this->agent->robot();
                    } elseif ($this->agent->is_mobile()) {
                        $agent = $this->agent->mobile();
                    } else {
                        $agent = 'Unidentified User Agent';
                    }

                    $data = [
                        'email_id' => $email->id,
                        'ip' => get_ip(),
                        'agent' => $agent,
                        'platform' => $this->agent->platform(),
                        'agent_string' => $this->agent->agent_string(),
                    ];
                    $email_track_id = $this->workflow_builder_log_email_tracks_model->save($data);
                    if (!$email_track_id) {
                        $this->db->trans_rollback();
                        exit();
                    }
                }

                //COMMIT
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    exit();
                }

                $this->db->trans_commit();
            }
        }
    }
}
