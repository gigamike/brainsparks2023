<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Assessment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('users_model');
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        return true;
    }

    public function index()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "assessment";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/steps/jquery.steps.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/steps/jquery.steps.min.js',
            asset_url() . 'js/assessment/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'assessment';
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
        $this->load->view('assessment/index', array_merge($view_data, $additional_data));
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

        $this->session->set_flashdata('error_message', "Unfortunately your love one has signs of Autism Spectrum Disorder. For help, you can consult to or smart chatbot, group support or schedule a call on doctor for telemedicine.");
       
        echo json_encode(['successful' => true]);
    }
}
