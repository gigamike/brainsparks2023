<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('settings_model');
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
        $view_data['user_menu'] = "users";

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
            asset_url() . 'js/settings/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'settings';
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
        $this->load->view('settings/index', $view_data);
        $this->load->view('template_footer', $view_data);
    }

    public function budget()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        $budget_threshold = $this->settings_model->getByName('budget_threshold');

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "settings";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */

        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/settings/budget.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'settings';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        // Overview defaults
        $view_data['budget_threshold'] = $budget_threshold;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('settings/budget', $view_data);
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_budget_threshold_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $amount = isset($dataset['amount']) ? trim($dataset['amount']) : 0;

        if(!is_numeric($amount)){
            $amount = 0;
        }

        //UPDATE
        //no need to check if there are seats left
        $setting = $this->settings_model->getByName('budget_threshold');
        if (!$setting) {
            echo json_encode(['successful' => false, 'error' => "Settings does not exists."]);
            return;
        }

        //START
        $this->db->trans_begin();

        $data = [];
        $data['id'] = $setting->id;
        $data['value'] = $amount;
        $setting_id = $this->settings_model->save($data);
        if (!$setting_id) {
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