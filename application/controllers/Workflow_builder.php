<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Workflow_builder extends CI_Controller
{
    protected $active_role = USER_ADMIN;
    protected $partner_data = null;
    protected $partner_super_agent = null;
    protected $_conditionAllowedTextFields = [
        // text

    ];
    protected $_conditionAllowedSelectFields = [

    ];
    protected $_conditionAllowedDateFields = [
        'date_added' => 'Customer Date Added',
        'date_modified' => 'Customer Date Modified',
        'date_of_birth' => 'Birth Date',
    ];
    protected $_conditionAllowedMultiFields = [
        'cutomer_age' => 'Customer Age',
    ];
    private $_allowedMediaFileTypes = [
        // Images
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('workflow_builder_model');
        $this->load->model('workflow_builder_email_template_model');
        $this->load->model('workflow_builder_sms_template_model');
        $this->load->model('workflow_builder_log_email_model');
        $this->load->model('workflow_builder_log_email_tracks_model');
        $this->load->model('log_sms_model');
        $this->load->model('workflow_builder_log_sms_model');
        $this->load->model('customers_model');

        $this->load->helper('ordinal');

        //INITILIASE VISIBLE COLUMN FILTERS
        $global_options = $this->session->utilihub_hub_global_view_options;

        if (!isset($global_options['partner_workflow_builder_columns'])) {
            $global_options['partner_workflow_builder_columns'] = ["0", "1", "2", "3", "4", "5", "6"];
        }

        if (!isset($global_options['partner_workflow_builder_email_template_columns'])) {
            $global_options['partner_workflow_builder_email_template_columns'] = ["0", "1", "2", "3"];
        }

        if (!isset($global_options['partner_workflow_builder_sms_template_columns'])) {
            $global_options['partner_workflow_builder_sms_template_columns'] = ["0", "1", "2", "3"];
        }

        if (!isset($global_options['settings_workflow_builder_columns_based_email_logs'])) {
            $global_options['settings_workflow_builder_columns_based_email_logs'] = ["0", "1", "2", "3", "4", "5"];
        }

        if (!isset($global_options['settings_workflow_builder_columns_based_sms_logs'])) {
            $global_options['settings_workflow_builder_columns_based_sms_logs'] = ["0", "1", "2", "3"];
        }

        $this->session->utilihub_hub_global_view_options = $global_options;
    }

    protected function validate_access($show_when_disabled = false)
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        if ($this->session->utilihub_hub_target_role != $this->active_role) {
            redirect(base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_target_role], 'refresh');
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
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css'
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/workflow-builder/workflow-builder.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];

        //kb explainer?
        $kb_code = 'workflow_builder';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        // Overview defaults
        $view_data['saved_filter'] = [];
        $global_options = $this->session->utilihub_hub_global_view_options;

        if (!isset($global_options['partner_workflow_builder_columns'])) {
            $global_options['partner_workflow_builder_columns'] = ["0", "1", "2", "3"];
        }

        if (!empty($this->partner_super_agent['overview_defaults'])) {
            $overviewDefaults = json_decode($this->partner_super_agent['overview_defaults'], true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $view_data['saved_filter'] = isset($overviewDefaults['partner_workflow_builder_overview']) ? $overviewDefaults['partner_workflow_builder_overview'] : '';

                // columns display
                if (isset($overviewDefaults['partner_workflow_builder_overview']['columnHeader']) && is_array($overviewDefaults['partner_workflow_builder_overview']['columnHeader'])) {
                    $global_options["partner_workflow_builder_columns"] = $overviewDefaults['partner_workflow_builder_overview']['columnHeader'];
                }
            }
        }
        $this->session->utilihub_hub_global_view_options = $global_options;

        $sidebar_data = [];
        $sidebar_data['sidebar_body'] = '';
        $sidebar_data['sidebar_body'] .= '';

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/main_workflow_builder', $view_data);
        $this->load->view('template_footer', array_merge($view_data, $sidebar_data));
    }

    public function ajax_dt_get_work_builder()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->get();
        $dt_params['columnHeader'] = isset($dt_params['columnHeader']) ? $dt_params['columnHeader'] : '';
        parse_str($dt_params['columnHeader'], $dt_params['columnHeader']);

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        if (isset($dt_params['filterNameOperator']) && !empty($dt_params['filterNameOperator']) && isset($dt_params['filterName']) && !empty($dt_params['filterName'])) {
            array_push($conditions_arr, stringify_condition("name", $dt_params['filterNameOperator'], $dt_params['filterName']));
            $filters_count++;
        }

        if (isset($dt_params['filterDateAddedOperator']) && !empty($dt_params['filterDateAddedOperator'])) {
            if ($dt_params['filterDateAddedOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateAddedFrom']) && !empty($dt_params['filterDateAddedFrom']) && isset($dt_params['filterDateAddedTo']) && !empty($dt_params['filterDateAddedTo'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAddedFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateAddedTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            } elseif (isset($dt_params['filterDateAdded']) && !empty($dt_params['filterDateAdded'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAdded'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            }
        }

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "name" => "name",
                "description" => "description",
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->workflow_builder_model->get_workflow_builder_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->workflow_builder_model->get_workflow_builder_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = $subset['id'];
            $dt_subset['DT_RowAttr'] = ['attr-workflow-builder' => $subset['id']];

            if (empty($subset['email_conversion_rate'])) {
                $dt_subset['email_conversion_rate'] = "0.00%";
            }

            $dt_subset['actions'] = $this->load->view('workflow_builder/section_workflow_builder_dt_actions', $dt_subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_set_workflow_builder_visible_cols()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->post();

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options['partner_workflow_builder_columns'] = array_values($this->input->post('columnHeader'));
        $this->session->utilihub_hub_global_view_options = $global_options;

        echo json_encode([]);
    }

    public function ajax_workflow_builder_deactivate()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dataset = $this->input->post();
        $dataset['workflow_builder_id'] = isset($dataset['workflow_builder_id']) ? $dataset['workflow_builder_id'] : null;

        if (empty($dataset['workflow_builder_id'])) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Workflow Builder',
            ]);
            return;
        }

        $workflow_builder = $this->workflow_builder_model->getById($dataset['workflow_builder_id']);
        if (!$workflow_builder) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Workflow Builder',
            ]);
            return;
        }

        //START
        $this->db->trans_begin();

        $new_workflow_builder = [
            'id' => $dataset['workflow_builder_id'],
            'active' => STATUS_NG,
        ];
        $workflow_builder_id = $this->workflow_builder_model->save($new_workflow_builder);
        if (!$workflow_builder_id) {
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

        echo json_encode([
            'successful' => true,
        ]);
    }

    public function ajax_workflow_builder_activate()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dataset = $this->input->post();
        $dataset['workflow_builder_id'] = isset($dataset['workflow_builder_id']) ? $dataset['workflow_builder_id'] : null;

        if (empty($dataset['workflow_builder_id'])) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Workflow Builder',
            ]);
            return;
        }

        $workflow_builder = $this->workflow_builder_model->getById($dataset['workflow_builder_id']);
        if (!$workflow_builder) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Workflow Builder',
            ]);
            return;
        }

        //START
        $this->db->trans_begin();

        $new_workflow_builder = [
            'id' => $dataset['workflow_builder_id'],
            'active' => STATUS_OK,
        ];
        $workflow_builder_id = $this->workflow_builder_model->save($new_workflow_builder);
        if (!$workflow_builder_id) {
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

        echo json_encode([
            'successful' => true,
        ]);
    }

    public function email_logs($workflow_builder_id = null)
    {
        if (!$this->validate_access()) {
            return;
        }

        if (empty($workflow_builder_id)) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        $workflow_builder = $this->workflow_builder_model->getById($workflow_builder_id);
        if (!$workflow_builder) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/workflow-builder/workflow-builder-email-log.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        // Overview defaults
        $view_data['saved_filter'] = [];
        $global_options = $this->session->utilihub_hub_global_view_options;

        if (!isset($global_options['settings_workflow_builder_columns_based_email_logs'])) {
            $global_options['settings_workflow_builder_columns_based_email_logs'] = ["0", "1", "2", "3", "4", "5"];
        }

        if (!empty($this->partner_super_agent['overview_defaults'])) {
            $overviewDefaults = json_decode($this->partner_super_agent['overview_defaults'], true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $view_data['saved_filter'] = isset($overviewDefaults['partner_workflow_builder_email_logs_overview']) ? $overviewDefaults['partner_workflow_builder_email_logs_overview'] : '';

                // columns display
                if (isset($overviewDefaults['partner_workflow_builder_email_logs_overview']['columnHeader']) && is_array($overviewDefaults['partner_workflow_builder_email_logs_overview']['columnHeader'])) {
                    $global_options["partner_workflow_builder_email_logs_columns"] = $overviewDefaults['partner_workflow_builder_email_logs_overview']['columnHeader'];
                }
            }
        }
        $this->session->utilihub_hub_global_view_options = $global_options;

        $additional_data = [];
        $additional_data['workflow_builder'] = $workflow_builder;

        $sidebar_data = [];
        $sidebar_data['sidebar_body'] = '';
        $sidebar_data['sidebar_body'] .= '';

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/email_log', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', array_merge($view_data, $sidebar_data));
    }

    public function ajax_dt_get_email_logs()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->get();
        $dt_params['columnHeader'] = isset($dt_params['columnHeader']) ? $dt_params['columnHeader'] : '';
        parse_str($dt_params['columnHeader'], $dt_params['columnHeader']);

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        $workflow_builder_id = isset($dt_params['workflow_builder_id']) ? $dt_params['workflow_builder_id'] : 0;

        array_push($conditions_arr, "tbl_workflow_builder_log.workflow_builder_id = " . $this->db->escape($workflow_builder_id));
        array_push($conditions_arr, "tbl_workflow_builder_log_email.processed = " . STATUS_OK);

        if (isset($dt_params['filterToOperator']) && !empty($dt_params['filterToOperator']) && isset($dt_params['filterTo']) && !empty($dt_params['filterTo'])) {
            array_push($conditions_arr, stringify_condition("tbl_workflow_builder_log_email.to", $dt_params['filterToOperator'], $dt_params['filterTo']));
            $filters_count++;
        }

        if (isset($dt_params['filterFromOperator']) && !empty($dt_params['filterFromOperator']) && isset($dt_params['filterFrom']) && !empty($dt_params['filterFrom'])) {
            array_push($conditions_arr, stringify_condition("tbl_workflow_builder_log_email.from", $dt_params['filterFromOperator'], $dt_params['filterFrom']));
            $filters_count++;
        }

        if (isset($dt_params['filterSubjectOperator']) && !empty($dt_params['filterSubjectOperator']) && isset($dt_params['filterSubject']) && !empty($dt_params['filterSubject'])) {
            array_push($conditions_arr, stringify_condition("tbl_workflow_builder_log_email.subject", $dt_params['filterSubjectOperator'], $dt_params['filterSubject']));
            $filters_count++;
        }

        if (isset($dt_params['filterDateSendOperator']) && !empty($dt_params['filterDateSendOperator'])) {
            if ($dt_params['filterDateSendOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateSendFrom']) && !empty($dt_params['filterDateSendFrom']) && isset($dt_params['filterDateSendTo']) && !empty($dt_params['filterDateSendTo'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_workflow_builder_log_email.processed", $dt_params['filterDateSendOperator'], reformat_str_date($dt_params['filterDateSendFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateSendTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            } elseif (isset($dt_params['filterDateSend']) && !empty($dt_params['filterDateSend'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_workflow_builder_log_email.processed", $dt_params['filterDateSendOperator'], reformat_str_date($dt_params['filterDateSend'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            }
        }

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "to" => "tbl_workflow_builder_log_email.to",
                "from" => "tbl_workflow_builder_log_email.from",
                "subject" => "tbl_workflow_builder_log_email.subject",
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->workflow_builder_log_email_model->get_email_log_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->workflow_builder_log_email_model->get_email_log_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = $subset['log_email_id'];
            $dt_subset['DT_RowAttr'] = ['attr-log-email-id' => $subset['log_email_id']];

            $dt_subset['actions'] = $this->load->view('workflow_builder/section_dt_actions_email_logs', $dt_subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_set_email_logs_visible_cols()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->post();

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options['settings_workflow_builder_columns_based_email_logs'] = array_values($this->input->post('columnHeader'));
        $this->session->utilihub_hub_global_view_options = $global_options;

        echo json_encode([]);
    }

    public function ajax_load_email_log()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $return_data = [];
        $log_email_id = $this->input->post('log_email_id');

        //get data
        $log_email = $this->workflow_builder_log_email_model->getById($log_email_id);

        $view_data = [];
        $view_data['log_email_id'] = $log_email->id;
        $view_data['subject'] = $log_email->subject;
        $view_data['from'] = $log_email->from;
        $view_data['to'] = $log_email->to;
        $view_data['cc'] = $log_email->cc;
        $view_data['bcc'] = $log_email->bcc;
        $view_data['date_processed'] = $log_email->date_processed;

        //if there are attachments
        $view_data['attachments_map'] = [];
        if ($log_email->attachment != null && $log_email->attachment != "") {
            $attachments = json_decode($log_email->attachment, true);
            // print_r($attachments);
            if (json_last_error() == JSON_ERROR_NONE) {
                if (is_array($attachments) && count($attachments) > 0) {
                    foreach ($attachments as $attachment) {
                        $pos = strrpos($attachment['fileUrl'], '/');
                        if ($pos) {
                            $filen = substr($attachment['fileUrl'], $pos + 1);
                            if ($filen && $filen != null && $filen != "") {
                                $view_data['attachments_map'][$filen] = $attachment['fileUrl'];
                            }
                        }
                    }
                }
            } else {
                $tmp_arr = explode(",", $log_email->attachment);
                foreach ($tmp_arr as $tmp_attach) {
                    $pos = strrpos($tmp_attach, '/');
                    if ($pos) {
                        $filen = substr($tmp_attach, $pos + 1);
                        if ($filen && $filen != null && $filen != "") {
                            $view_data['attachments_map'][$filen] = $tmp_attach;
                        }
                    }
                }
            }
        }

        $filter = [
            'email_id' => $log_email->id,
        ];
        $email_track = $this->workflow_builder_log_email_tracks_model->fetch($filter, [], 1, null);
        $view_data['email_track'] = $email_track;

        $return_data['html'] = $this->load->view('workflow_builder/section_email_log_modal', $view_data, true);
        echo json_encode($return_data);
    }

    public function email_html($log_email_id = 0)
    {
        $this->validate_access();

        if (empty($log_email_id)) {
            $this->load->view('errors/restricted_page');
            return;
        }

        $currentLogEmail = $this->workflow_builder_log_email_model->getById($log_email_id);
        if (!$currentLogEmail) {
            $this->load->view('errors/restricted_page');
            return;
        }

        echo $currentLogEmail->html_message;
    }

    public function sms_logs($workflow_builder_id = null)
    {
        if (!$this->validate_access()) {
            return;
        }

        if (empty($workflow_builder_id)) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        $workflow_builder = $this->workflow_builder_model->getById($workflow_builder_id);
        if (!$workflow_builder) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/workflow-builder/workflow-builder-sms-log.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];
        $additional_data['workflow_builder'] = $workflow_builder;

        $sidebar_data = [];
        $sidebar_data['sidebar_body'] = '';
        $sidebar_data['sidebar_body'] .= '';

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('workflow_builder/sms_log', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', array_merge($view_data, $sidebar_data));
    }

    public function ajax_dt_get_sms_logs()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->get();

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        $workflow_builder_id = isset($dt_params['workflow_builder_id']) ? $dt_params['workflow_builder_id'] : 0;

        array_push($conditions_arr, "tbl_workflow_builder_log.workflow_builder_id = " . $this->db->escape($workflow_builder_id));
        array_push($conditions_arr, "tbl_workflow_builder_log_sms.processed = " . STATUS_OK);

        if (isset($dt_params['filterToOperator']) && !empty($dt_params['filterToOperator']) && isset($dt_params['filterTo']) && !empty($dt_params['filterTo'])) {
            array_push($conditions_arr, stringify_condition("tbl_workflow_builder_log_sms.to", $dt_params['filterToOperator'], $dt_params['filterTo']));
            $filters_count++;
        }

        if (isset($dt_params['filterFromOperator']) && !empty($dt_params['filterFromOperator']) && isset($dt_params['filterFrom']) && !empty($dt_params['filterFrom'])) {
            array_push($conditions_arr, stringify_condition("tbl_workflow_builder_log_sms.from", $dt_params['filterFromOperator'], $dt_params['filterFrom']));
            $filters_count++;
        }

        if (isset($dt_params['filterDateSentOperator']) && !empty($dt_params['filterDateSentOperator'])) {
            if ($dt_params['filterDateSentOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateSentFrom']) && !empty($dt_params['filterDateSentFrom']) && isset($dt_params['filterDateSentTo']) && !empty($dt_params['filterDateSentTo'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_workflow_builder_log_sms.date_processed", $dt_params['filterDateSentOperator'], reformat_str_date($dt_params['filterDateSentFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateSentTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            } elseif (isset($dt_params['filterDateSent']) && !empty($dt_params['filterDateSent'])) {
                array_push($conditions_arr, stringify_date_condition("tbl_workflow_builder_log_sms.date_processed", $dt_params['filterDateSentOperator'], reformat_str_date($dt_params['filterDateSent'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            }
        }

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "to" => "tbl_workflow_builder_log_sms.to",
                "from" => "tbl_workflow_builder_log_sms.from",
                "message" => "tbl_workflow_builder_log_sms.message",
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->workflow_builder_log_sms_model->get_sms_log_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->workflow_builder_log_sms_model->get_sms_log_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = $subset['log_sms_id'];
            $dt_subset['DT_RowAttr'] = ['attr-log-sms-id' => $subset['log_sms_id']];

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_set_sms_logs_visible_cols()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options['settings_workflow_builder_columns_based_sms_logs'] = array_values($this->input->post('columnHeader'));
        $this->session->utilihub_hub_global_view_options = $global_options;
        echo json_encode([]);
    }

    public function workflow_builder_add()
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
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/steps/jquery.steps.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
            asset_url() . 'css/plugins/clockpicker/clockpicker.css',
            asset_url() . 'css/plugins/iCheck/line/line.css',
            asset_url() . 'css/plugins/iCheck/square/blue.css',
            asset_url() . 'css/plugins/switchery/switchery.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/steps/jquery.steps.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/plugins/clockpicker/clockpicker.js',
            asset_url() . 'js/plugins/iCheck/icheck.min.js',
            asset_url() . 'js/plugins/switchery/switchery.js',
            asset_url() . 'js/workflow-builder/workflow-builder-add.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        //kb explainer?
        $kb_code = 'workflow_builder_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $filter = [
            'date_deleted_is_empty' => true,
        ];
        $order = [
            'name',
        ];
        $view_data['email_templates'] = $this->workflow_builder_email_template_model->fetch($filter, $order);
        $view_data['sms_templates'] = $this->workflow_builder_sms_template_model->fetch($filter, $order);

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function workflow_builder_update($workflow_builder_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        if (empty($workflow_builder_id)) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        $workflow_builder = $this->workflow_builder_model->getById($workflow_builder_id);
        if (!$workflow_builder) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/steps/jquery.steps.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
            asset_url() . 'css/plugins/clockpicker/clockpicker.css',
            asset_url() . 'css/plugins/iCheck/line/line.css',
            asset_url() . 'css/plugins/iCheck/square/blue.css',
            asset_url() . 'css/plugins/switchery/switchery.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/steps/jquery.steps.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/plugins/clockpicker/clockpicker.js',
            asset_url() . 'js/plugins/iCheck/icheck.min.js',
            asset_url() . 'js/plugins/switchery/switchery.js',
            asset_url() . 'js/workflow-builder/workflow-builder-update.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];
        $additional_data['workflow_builder'] = $workflow_builder;
        $additional_data['workflow_builder_json_dataset'] = json_decode($workflow_builder->json_dataset, true);
        $additional_data['partner_data'] = $this->partner_data;
        $additional_data['partner_super_agent'] = $this->partner_super_agent;

        // CONDITIONS
        $view_data['condition_allowed_text_fields'] = $this->_conditionAllowedTextFields;
        $view_data['condition_allowed_select_fields'] = $this->_conditionAllowedSelectFields;
        $view_data['condition_allowed_date_fields'] = $this->_conditionAllowedDateFields;
        $view_data['condition_allowed_multi_fields'] = $this->_conditionAllowedMultiFields;

        $tempFields = $additional_data['workflow_builder_json_dataset']['fields'];
        $tempValues = $additional_data['workflow_builder_json_dataset']['values'];
        $valueApplicationStatus = null;
        $selectedFields = [];

        if (count($tempFields) > 0) {
            foreach ($tempFields as $tempFieldKey => $tempFieldValue) {
                $selectedFields[] = $tempFieldKey;
                if ($tempFieldValue == 'application_status') {
                    $valueApplicationStatus = $tempValues[$tempFieldKey];
                }
            }
        }

        $view_data['applicationStatusSelected'] = $valueApplicationStatus;

        $allowedFields = array_merge($this->_conditionAllowedTextFields, $this->_conditionAllowedSelectFields, $this->_conditionAllowedDateFields, $this->_conditionAllowedMultiFields);
        asort($allowedFields);
        /*
          if (count($selectedFields) > 0) {
          foreach ($selectedFields as $selectedField) {
          unset($allowedFields[$selectedField]);
          }
          }
          if (!in_array('application_status', $selectedFields)) {
          unset($allowedFields['application_status_tag']);
          }
         */
        $additional_data['condition_allowed_fields'] = $allowedFields;

        $filter = [
            'date_deleted_is_empty' => true,
        ];
        $order = [
            'name',
        ];
        $view_data['email_templates'] = $this->workflow_builder_email_template_model->fetch($filter, $order);
        $view_data['sms_templates'] = $this->workflow_builder_sms_template_model->fetch($filter, $order);

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_update', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_workflow_builder_condition_add()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['condition_index'] = isset($dataset['condition_index']) ? $dataset['condition_index'] : 1;
        $dataset['selectedFields'] = isset($dataset['selectedFields']) ? $dataset['selectedFields'] : [];

        $view_data = [];
        $json_data = [];

        $allowedFields = array_merge($this->_conditionAllowedTextFields, $this->_conditionAllowedSelectFields, $this->_conditionAllowedDateFields, $this->_conditionAllowedMultiFields);
        if (count($dataset['selectedFields']) > 0) {
            foreach ($dataset['selectedFields'] as $selectedField) {
                unset($allowedFields[$selectedField]);
            }
        }
        if (!in_array('application_status', $dataset['selectedFields'])) {
            unset($allowedFields['application_status_tag']);
        }

        asort($allowedFields);
        $view_data['condition_index'] = $dataset['condition_index'];
        $view_data['condition_allowed_fields'] = $allowedFields;

        $json_data['html'] = $this->load->view('workflow_builder/section_workflow_builder_condition_add', $view_data, true);

        echo json_encode($json_data);
    }

    public function ajax_workflow_builder_condition_add_field()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['condition_index'] = isset($dataset['condition_index']) ? $dataset['condition_index'] : 1;
        $dataset['field'] = isset($dataset['field']) ? $dataset['field'] : null;
        $dataset['applicationStatusSelected'] = isset($dataset['applicationStatusSelected']) ? $dataset['applicationStatusSelected'] : null;

        $view_data = [];
        $view_data['condition_index'] = $dataset['condition_index'];
        $view_data['field'] = $dataset['field'];

        $view_data['condition_allowed_text_fields'] = $this->_conditionAllowedTextFields;
        $view_data['condition_allowed_select_fields'] = $this->_conditionAllowedSelectFields;
        $view_data['condition_allowed_date_fields'] = $this->_conditionAllowedDateFields;
        $view_data['condition_allowed_multi_fields'] = $this->_conditionAllowedMultiFields;

        $json_data = [];

        $json_data['html'] = $this->load->view('workflow_builder/section_workflow_builder_condition_add_field', $view_data, true);

        echo json_encode($json_data);
    }

    public function ajax_workflow_builder_action_option_add_email_template()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['email_template_id'] = isset($dataset['email_template_id']) ? $dataset['email_template_id'] : null;
        if (!$dataset['email_template_id']) {
            echo json_encode(['successful' => false, 'error' => "Invalid Email Template"]);
            return;
        }

        $email_template = $this->workflow_builder_email_template_model->getById($dataset['email_template_id']);
        if (!$email_template) {
            echo json_encode(['successful' => false, 'error' => "Invalid Email Template"]);
            return;
        }

        echo json_encode([
            'successful' => true,
            'subject' => $email_template->subject,
            'html_template' => $email_template->html_template,
        ]);
    }

    public function ajax_workflow_builder_action_option_add_sms_template()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['sms_template_id'] = isset($dataset['sms_template_id']) ? $dataset['sms_template_id'] : null;
        if (!$dataset['sms_template_id']) {
            echo json_encode(['successful' => false, 'error' => "Invalid SMS Template"]);
            return;
        }

        $sms_template = $this->workflow_builder_sms_template_model->getById($dataset['sms_template_id']);
        if (!$sms_template) {
            echo json_encode(['successful' => false, 'error' => "Invalid SMS Template"]);
            return;
        }

        echo json_encode([
            'successful' => true,
            'template' => $sms_template->template,
        ]);
    }

    public function ajax_workflow_builder_action_option_get_application_status_tag()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['application_status'] = isset($dataset['application_status']) ? $dataset['application_status'] : null;

        $view_data = [];

        $status_tags = [];
        if ($dataset['application_status']) {
            $status_tags_per_application_status = [];
            foreach ($this->config->item('mm8_status_tags_map')[$dataset['application_status']] as $item) {
                $status_tags_per_application_status[] = $item;
            }
            $status_tags = array_merge($status_tags, $status_tags_per_application_status);
        }

        $view_data['status_tag_list'] = array_reduce($status_tags, function ($result, $item) {
            $result[$item] = $this->config->item('mm8_status_tags')[$item];
            return $result;
        }, []);

        echo json_encode(['successful' => true, 'html' => $this->load->view('workflow_builder/section_workflow_builder_action_option_status_tag', $view_data, true)]);
    }

    /*
     *
     * https://tili-group.monday.com/boards/767690602/pulses/783024011?term=783024011
     * Update Workflow: - At present, when you select "Application Date Added", greater than "Date", a user can update the application. If you change the status to "Partial" / "Call Scheduled", the risk here is that the system will not have a time to call and the lead will be lost as everything must have a date and time allocated to the application. My suggestion is that if the above is done, the user needs to input the number of days after the application date is added which then updates the record to the number of days entered from the date the application is added.
     *
     */

    public function ajax_workflow_builder_action_option_get_application_status_tag_no_of_days()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['fields'] = isset($dataset['fields']) ? $dataset['fields'] : [];
        $dataset['conditions'] = isset($dataset['conditions']) ? $dataset['conditions'] : [];
        $dataset['values'] = isset($dataset['values']) ? $dataset['values'] : [];
        $dataset['action_application_status'] = isset($dataset['action_application_status']) ? $dataset['action_application_status'] : null;
        $dataset['action_application_status_tag'] = isset($dataset['action_application_status_tag']) ? $dataset['action_application_status_tag'] : null;

        $isDateAddedFound = false;
        if (count($dataset['fields']) > 0) {
            foreach ($dataset['fields'] as $keyField => $field) {
                if ($field == 'date_added') {
                    if ($dataset['conditions'][$keyField] == QUERY_FILTER_GREATER_THAN || $dataset['conditions'][$keyField] == QUERY_FILTER_GREATER_THAN_OR_EQUAL) {
                        $isDateAddedFound = true;
                    }
                }
            }
        }

        if (!$isDateAddedFound) {
            echo json_encode(['successful' => false]);
            return;
        }

        if ($dataset['action_application_status'] != OPEN) {
            echo json_encode(['successful' => false]);
            return;
        }

        switch ($dataset['action_application_status_tag']) {
            case CALL_SCHEDULED_1:
                break;
            case CALL_SCHEDULED_2:
                break;
            case CALL_SCHEDULED_3:
                break;
            default:
                echo json_encode(['successful' => false]);
                return;
                break;
        }

        $view_data = [];

        echo json_encode(['successful' => true]);
    }

    public function ajax_workflow_builder_validate_fields()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();

        $results = $this->_validateFields($dataset);

        echo json_encode($results);
        return;
    }

    private function _validateFields($dataset)
    {
        // Step 2 Conditions
        $dataset['fields'] = isset($dataset['fields']) ? $dataset['fields'] : [];
        $dataset['conditions'] = isset($dataset['conditions']) ? $dataset['conditions'] : [];
        $dataset['values'] = isset($dataset['values']) ? $dataset['values'] : [];
        $dataset['subconditions'] = isset($dataset['subconditions']) ? $dataset['subconditions'] : [];

        // print_r($dataset);

        $tempFields = $dataset['fields'];
        $tempValues = $dataset['values'];

        $ctrField = 0;
        if (count($dataset['fields']) > 0) {
            foreach ($dataset['fields'] as $key => $field) {
                $ctrField++;

                if ($ctrField == 1) {
                    // no subconditions
                } else {
                    switch ($dataset['subconditions'][$key]) {
                        case 'AND':
                            break;
                        case 'OR':
                            break;
                        default:

                            return [
                                'successful' => false,
                                'error' => 'Invaid Subconditions',
                            ];
                    }
                }

                if (empty($dataset['fields'][$key])) {
                    return [
                        'successful' => false,
                        'error' => 'Invalid Fields',
                    ];
                }

                if (empty($dataset['conditions'][$key])) {
                    return [
                        'successful' => false,
                        'error' => 'Invalid Conditions',
                    ];
                }

                if (array_key_exists($dataset['fields'][$key], $this->_conditionAllowedTextFields)) {
                    // text
                    if (!in_array($dataset['conditions'][$key], $this->config->item('mm8_filter_string_operator'))) {
                        return [
                            'successful' => false,
                            'error' => 'Invalid Condition. Invalid field text condition',
                        ];
                    }
                } elseif (array_key_exists($dataset['fields'][$key], $this->_conditionAllowedDateFields)) {
                    // dates
                    if ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_DAY') {
                        if (!is_numeric($dataset['values'][$key]['date_add'])) {
                            return [
                                'successful' => false,
                                'error' => 'Invalid Condition. Invalid field condition Plus Days',
                            ];
                        }
                    } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_MONTH') {
                        if (!is_numeric($dataset['values'][$key]['date_add'])) {
                            return [
                                'successful' => false,
                                'error' => 'Invalid Condition. Invalid field condition Plus Months',
                            ];
                        }
                    } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_YEAR') {
                        if (!is_numeric($dataset['values'][$key]['date_add'])) {
                            return [
                                'successful' => false,
                                'error' => 'Invalid Condition. Invalid field condition Plus Year',
                            ];
                        }
                    } elseif ($dataset['conditions'][$key] == 'DATE_MINUS_INTERVAL_DAY') {
                        if (!is_numeric($dataset['values'][$key]['date_add'])) {
                            return [
                                'successful' => false,
                                'error' => 'Invalid Condition. Invalid field condition Minus Days',
                            ];
                        }
                    } elseif (!in_array($dataset['conditions'][$key], $this->config->item('mm8_filter_dates_operator'))) {
                        return [
                            'successful' => false,
                            'error' => 'Invalid Condition. Invalid field date condition',
                        ];

                        if ($dataset['conditions'][$key] == 13) {
                            //QUERY_FILTER_IS_BETWEEN = 13
                            if (empty($dataset['values'][$key]['date_from']) || empty($dataset['values'][$key]['date_to'])) {
                                return [
                                    'successful' => false,
                                    'error' => 'Invalid Condition. Invalid field condition between date',
                                ];
                            }
                        } else {
                            if (empty($dataset['values'][$key]['date_single'])) {
                                return [
                                    'successful' => false,
                                    'error' => 'Invalid Condition. Invalid field condition date',
                                ];
                            }
                        }
                    }
                } else {
                    /*
                    return [
                        'successful' => false,
                        'error' => 'Invalid Field',
                    ];
                    */
                }
            }
        }

        return [
            'successful' => true,
            'error' => 'None',
        ];
    }

    public function ajax_workflow_builder_validate_actions()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();

        $results = $this->_validateActions($dataset);

        echo json_encode($results);
    }

    private function _validateActions($dataset)
    {
        // Step 3 Actions
        $dataset['action_email_send'] = isset($dataset['action_email_send']) ? $dataset['action_email_send'] : 0;
        $dataset['action_email_template_id'] = isset($dataset['action_email_template_id']) ? $dataset['action_email_template_id'] : null;
        $dataset['action_email_subject'] = isset($dataset['action_email_subject']) ? $dataset['action_email_subject'] : null;
        $dataset['action_email_html_template'] = isset($dataset['action_email_html_template']) ? $dataset['action_email_html_template'] : null;
        $dataset['action_email_with_system_template'] = isset($dataset['action_email_with_system_template']) ? $dataset['action_email_with_system_template'] : 0;

        $dataset['action_sms_send'] = isset($dataset['action_sms_send']) ? $dataset['action_sms_send'] : 0;
        $dataset['action_sms_template_id'] = isset($dataset['action_sms_template_id']) ? $dataset['action_sms_template_id'] : null;
        $dataset['action_sms_message'] = isset($dataset['action_sms_message']) ? $dataset['action_sms_message'] : null;

        // make sure there is an Action
        if (!$dataset['action_email_send'] && !$dataset['action_sms_send'] && !$dataset['action_set_application']) {
            return [
                'successful' => false,
                'error' => 'Please select at least 1 action',
            ];
        }

        if ($dataset['action_email_send']) {
            if ($dataset['action_email_subject'] == '') {
                return [
                    'successful' => false,
                    'error' => 'Required field Email Subject',
                ];
            }
        }

        if ($dataset['action_sms_send']) {
            if ($dataset['action_sms_message'] == '') {
                return [
                    'successful' => false,
                    'error' => 'Required field SMS message',
                ];
            }
        }

        return [
            'successful' => true,
            'error' => 'None',
        ];
    }

    public function ajax_workflow_builder_execution()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();

        $dataset['workflow_builder_id'] = isset($dataset['workflow_builder_id']) ? trim($dataset['workflow_builder_id']) : null;

        // Step 3 Actions
        $dataset['action_email_send'] = isset($dataset['action_email_send']) ? $dataset['action_email_send'] : 0;

        $dataset['action_sms_send'] = isset($dataset['action_sms_send']) ? $dataset['action_sms_send'] : 0;

        $dataset['action_set_application'] = isset($dataset['action_set_application']) ? $dataset['action_set_application'] : 0;

        // Step 4 Executions
        $dataset['execute_run'] = isset($dataset['execute_run']) ? trim($dataset['execute_run']) : null;

        $return_data = [];

        $view_data = [];

        if (!empty($dataset['workflow_builder_id'])) {
            $view_data['workflow_builder'] = $this->workflow_builder_model->getById($dataset['workflow_builder_id']);
        }

        $view_data['action_email_send'] = $dataset['action_email_send'];
        $view_data['action_sms_send'] = $dataset['action_sms_send'];
        $view_data['action_set_application'] = $dataset['action_set_application'];
        $view_data['execute_run'] = $dataset['execute_run'];

        $return_data['html'] = $this->load->view('workflow_builder/section_execution', $view_data, true);

        echo json_encode($return_data);
    }

    public function ajax_workflow_builder_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $dataset['workflow_builder_id'] = isset($dataset['workflow_builder_id']) ? trim($dataset['workflow_builder_id']) : null;

        // Step 1 Details
        $dataset['name'] = isset($dataset['name']) ? trim($dataset['name']) : null;
        $dataset['description'] = isset($dataset['description']) ? trim($dataset['description']) : null;

        // Step 2 Conditions
        $dataset['fields'] = isset($dataset['fields']) ? $dataset['fields'] : [];
        $dataset['conditions'] = isset($dataset['conditions']) ? $dataset['conditions'] : [];
        $dataset['values'] = isset($dataset['values']) ? $dataset['values'] : [];
        $dataset['subconditions'] = isset($dataset['subconditions']) ? $dataset['subconditions'] : [];

        // Step 3 Actions
        $dataset['action_email_send'] = isset($dataset['action_email_send']) ? $dataset['action_email_send'] : 0;
        $dataset['action_email_template_id'] = isset($dataset['action_email_template_id']) ? $dataset['action_email_template_id'] : null;
        $dataset['action_email_subject'] = isset($dataset['action_email_subject']) ? $dataset['action_email_subject'] : null;
        $dataset['action_email_html_template'] = isset($dataset['action_email_html_template']) ? $dataset['action_email_html_template'] : null;
        $dataset['action_email_with_system_template'] = isset($dataset['action_email_with_system_template']) ? $dataset['action_email_with_system_template'] : 0;

        $dataset['action_sms_send'] = isset($dataset['action_sms_send']) ? $dataset['action_sms_send'] : 0;
        $dataset['action_sms_template_id'] = isset($dataset['action_sms_template_id']) ? $dataset['action_sms_template_id'] : null;
        $dataset['action_sms_message'] = isset($dataset['action_sms_message']) ? $dataset['action_sms_message'] : null;

        // Step 4 Executions
        $dataset['execute'] = isset($dataset['execute']) ? trim($dataset['execute']) : null;
        $dataset['execute_run'] = isset($dataset['execute_run']) ? trim($dataset['execute_run']) : null;
        $dataset['execute_day'] = isset($dataset['execute_day']) ? trim($dataset['execute_day']) : null;
        $dataset['execute_month'] = isset($dataset['execute_month']) ? trim($dataset['execute_month']) : null;
        $dataset['execute_time'] = isset($dataset['execute_time']) ? trim($dataset['execute_time']) : null;

        if ($dataset['name'] == '') {
            echo json_encode([
                'successful' => false,
                'error' => 'Required field name',
            ]);
            return;
        }

        if ($dataset['description'] == '') {
            echo json_encode([
                'successful' => false,
                'error' => 'Required field name',
            ]);
            return;
        }

        $results = $this->_validateFields($dataset);
        if (!$results['successful']) {
            echo json_encode($results);
            return;
        }

        $results = $this->_validateActions($dataset);
        if (!$results['successful']) {
            echo json_encode($results);
            return;
        }

        // make sure there is an Execute
        if (empty($dataset['execute'])) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Execution',
            ]);
            return;
        }

        if (empty($dataset['execute_run'])) {
            echo json_encode([
                'successful' => false,
                'error' => 'Invalid Execution Run',
            ]);
            return;
        }

        switch ($dataset['execute_run']) {
            case 'once':
                break;
            case 'recurrent':
                if ($dataset['action_email_send'] == STATUS_OK || $dataset['action_sms_send'] == STATUS_OK) {
                    switch ($dataset['execute']) {
                        case 'every_day':
                            break;
                        case 'every_week':
                            break;
                        case 'every_month':
                            break;
                        case 'every_5_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_10_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_15_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_20_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_25_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_30_minutes':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_1_hour':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_2_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_3_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_4_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_5_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_6_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_7_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_8_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_9_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_10_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_11_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        case 'every_12_hours':
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                            break;
                        default:
                            echo json_encode([
                                'successful' => false,
                                'error' => 'Invalid Execution',
                            ]);
                            return;
                    }
                }
                break;
            default:
                echo json_encode([
                    'successful' => false,
                    'error' => 'Invalid Execution Run',
                ]);
                return;
        }

        switch ($dataset['execute']) {
            case 'every_day':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;

                if (empty($dataset['execute_time'])) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Required field Execution Time',
                    ]);
                    return;
                }

                if (strpos($dataset['execute_time'], ':') === false) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time',
                    ]);
                    return;
                }
                list($hour, $minute) = explode(":", $dataset['execute_time']);
                if (!is_numeric($hour) || !is_numeric($minute)) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour and minute',
                    ]);
                    return;
                }
                if (!in_array($hour, range(0, 23))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour',
                    ]);
                    return;
                }
                if (!in_array($minute, range(0, 59))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Minute',
                    ]);
                    return;
                }
                $dataset['execute_time_hour'] = $hour;
                $dataset['execute_time_minute'] = $minute;
                break;
            case 'every_week':
                if (empty($dataset['execute_weekday'])) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Week Day',
                    ]);
                    return;
                }

                $dataset['execute_day_of_month'] = null;

                if (empty($dataset['execute_time'])) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Required field Execution Time',
                    ]);
                    return;
                }

                if (strpos($dataset['execute_time'], ':') === false) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time',
                    ]);
                    return;
                }
                list($hour, $minute) = explode(":", $dataset['execute_time']);
                if (!is_numeric($hour) || !is_numeric($minute)) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour and minute',
                    ]);
                    return;
                }
                if (!in_array($hour, range(0, 23))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour',
                    ]);
                    return;
                }
                if (!in_array($minute, range(0, 59))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Minute',
                    ]);
                    return;
                }
                $dataset['execute_time_hour'] = $hour;
                $dataset['execute_time_minute'] = $minute;
                break;
            case 'every_month':
                if (empty($dataset['execute_day_of_month'])) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Day of Month',
                    ]);
                    return;
                }

                $dataset['execute_weekday'] = null;

                if (empty($dataset['execute_time'])) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Required field Execution Time',
                    ]);
                    return;
                }

                if (strpos($dataset['execute_time'], ':') === false) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time',
                    ]);
                    return;
                }
                list($hour, $minute) = explode(":", $dataset['execute_time']);
                if (!is_numeric($hour) || !is_numeric($minute)) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour and minute',
                    ]);
                    return;
                }
                if (!in_array($hour, range(0, 23))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Hour',
                    ]);
                    return;
                }
                if (!in_array($minute, range(0, 59))) {
                    echo json_encode([
                        'successful' => false,
                        'error' => 'Invalid Execution Time Minute',
                    ]);
                    return;
                }
                $dataset['execute_time_hour'] = $hour;
                $dataset['execute_time_minute'] = $minute;
                break;
            case 'every_5_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_10_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_15_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_20_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_25_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_30_minutes':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_1_hour':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_2_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_3_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_4_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_5_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_6_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_7_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_8_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_9_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_10_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_11_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            case 'every_12_hours':
                $dataset['execute_weekday'] = null;
                $dataset['execute_day_of_month'] = null;
                $dataset['execute_time_hour'] = null;
                $dataset['execute_time_minute'] = null;
                break;
            default:
                echo json_encode([
                    'successful' => false,
                    'error' => 'Invalid Execution',
                ]);
                return;
        }

        if (!empty($dataset['workflow_builder_id'])) {
            // UPDATE
            //no need to check if there are seats left

            $workflow_builder = $this->workflow_builder_model->getById($dataset['workflow_builder_id']);
            if (!$workflow_builder) {
                echo json_encode([
                    'successful' => false,
                    'error' => 'Invalid Workflow Builder',
                ]);
                return;
            }

            //START
            $this->db->trans_begin();

            $new_workflow_builder = [
                'id' => $dataset['workflow_builder_id'],
                'name' => $dataset['name'],
                'description' => $dataset['description'],
                'json_dataset' => json_encode($this->input->post()),
                'sql_statement' => $this->_generateSqlStatement($dataset),
                'action_email_send' => $dataset['action_email_send'],
                'action_email_template_id' => $dataset['action_email_template_id'],
                'action_email_subject' => $dataset['action_email_subject'],
                'action_email_html_body' => $dataset['action_email_html_template'],
                'action_email_with_system_template' => $dataset['action_email_with_system_template'],
                'action_sms_send' => $dataset['action_sms_send'],
                'action_sms_template_id' => $dataset['action_sms_template_id'],
                'action_sms_message' => $dataset['action_sms_message'],
                'execute' => $dataset['execute'],
                'execute_run' => $dataset['execute_run'],
                'execute_weekday' => $dataset['execute_weekday'],
                'execute_day_of_month' => $dataset['execute_day_of_month'],
                'execute_time_hour' => $dataset['execute_time_hour'],
                'execute_time_minute' => $dataset['execute_time_minute'],
            ];
            $workflow_builder_id = $this->workflow_builder_model->save($new_workflow_builder);
            if (!$workflow_builder_id) {
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
        } else {
            //INSERT NEW
            //START
            $this->db->trans_begin();

            $new_workflow_builder = [
                'name' => $dataset['name'],
                'description' => $dataset['description'],
                'json_dataset' => json_encode($this->input->post()),
                'sql_statement' => $this->_generateSqlStatement($dataset),
                'action_email_send' => $dataset['action_email_send'],
                'action_email_template_id' => $dataset['action_email_template_id'],
                'action_email_subject' => $dataset['action_email_subject'],
                'action_email_html_body' => $dataset['action_email_html_template'],
                'action_email_with_system_template' => $dataset['action_email_with_system_template'],
                'action_sms_send' => $dataset['action_sms_send'],
                'action_sms_template_id' => $dataset['action_sms_template_id'],
                'action_sms_message' => $dataset['action_sms_message'],
                'execute' => $dataset['execute'],
                'execute_run' => $dataset['execute_run'],
                'execute_weekday' => $dataset['execute_weekday'],
                'execute_day_of_month' => $dataset['execute_day_of_month'],
                'execute_time_hour' => $dataset['execute_time_hour'],
                'execute_time_minute' => $dataset['execute_time_minute'],
                'active' => STATUS_OK,
            ];
            $workflow_builder_id = $this->workflow_builder_model->save($new_workflow_builder);
            if (!$workflow_builder_id) {
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
        }

        echo json_encode([
            'successful' => true,
        ]);
    }

    protected function _generateSqlStatement($dataset, $isConditionOnly = false)
    {
        $sql = "SELECT DISTINCT(tbl_customers.id) AS customer_id";
        $sql .= " FROM tbl_customers";
        ;

        $sql_conditions = [];

        array_push($sql_conditions, "(tbl_customers.date_unsubscribed IS NULL OR TRIM(tbl_customers.date_unsubscribed)='') AND ");

        foreach ($dataset['fields'] as $key => $field) {
            switch ($field) {
                case 'date_of_birth':
                    // Customer Date of Birth
                    $field_name = "tbl_customers.date_of_birth";
                    $sql_conditions = $this->_generateSqlStatementDate($key, $field_name, $dataset, $sql_conditions);
                    break;
                case 'cutomer_age':
                    // Customer age via Date of Birth
                    $field_name = "tbl_customers.date_of_birth";
                    $sql_conditions = $this->_generateSqlStatementAge($key, $field_name, $dataset, $sql_conditions);
                    break;
                case 'date_added':
                    // Application Date Added
                    $field_name = "tbl_customers.date_added";
                    break;
                case 'date_modified':
                    // Application Date Modified
                    $field_name = "tbl_customers.date_modified";
                    $sql_conditions = $this->_generateSqlStatementDate($key, $field_name, $dataset, $sql_conditions);
                    break;
                default:
                    break;
            }
        }


        if ($isConditionOnly) {
            return $sql_conditions;
        }

        if (count($sql_conditions) > 0) {
            $sql .= " WHERE " . implode(" ", $sql_conditions);
        }

        // echo $sql; exit();

        return $sql;
    }

    private function _generateSqlStatementText($key, $field_name, $dataset, $sql_conditions)
    {
        if ($key == 1) {
            array_push(
                $sql_conditions,
                stringify_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    is_array($dataset['values'][$key]) ? implode(',', $dataset['values'][$key]) : $dataset['values'][$key]
                )
            );
        } else {
            array_push(
                $sql_conditions,
                $dataset['subconditions'][$key]
                . " ("
                . stringify_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    is_array($dataset['values'][$key]) ? implode(',', $dataset['values'][$key]) : $dataset['values'][$key]
                )
                . ")"
            );
        }

        return $sql_conditions;
    }

    private function _generateSqlStatementAge($key, $field_name, $dataset, $sql_conditions)
    {
        if ($key == 1) {
            array_push($sql_conditions, stringify_age_condition($key, $field_name, $dataset['conditions'], $dataset['values']));
        } else {
            array_push($sql_conditions, $dataset['subconditions'][$key] . " (".stringify_age_condition($key, $field_name, $dataset['conditions'], $dataset['values']) . ") ");
        }

        return $sql_conditions;
    }

    private function _generateSqlStatementDate($key, $field_name, $dataset, $sql_conditions)
    {
        if ($key == 1) {
            if ($dataset['conditions'][$key] == QUERY_FILTER_IS_BETWEEN) {
                array_push($sql_conditions, stringify_date_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    reformat_str_date($dataset['values'][$key]['date_from'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'),
                    reformat_str_date($dataset['values'][$key]['date_to'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')
                ));
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_DAY') {
                array_push($sql_conditions, "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " DAY)) = CURDATE()");
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_MONTH') {
                array_push($sql_conditions, "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " MONTH)) = CURDATE()");
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_YEAR') {
                array_push($sql_conditions, "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " YEAR)) = CURDATE()");
            } elseif ($dataset['conditions'][$key] == 'DATE_MINUS_INTERVAL_DAY') {
                array_push($sql_conditions, "DATE(DATE_SUB(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " YEAR)) = CURDATE()");
            } else {
                array_push($sql_conditions, stringify_date_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    reformat_str_date($dataset['values'][$key]['date_single'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')
                ));
            }
        } else {
            if ($dataset['conditions'][$key] == QUERY_FILTER_IS_BETWEEN) {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . stringify_date_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    reformat_str_date($dataset['values'][$key]['date_from'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'),
                    reformat_str_date($dataset['values'][$key]['date_to'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')
                ) . ")");
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_DAY') {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " DAY)) = CURDATE())");
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_MONTH') {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " MONTH)) = CURDATE())");
            } elseif ($dataset['conditions'][$key] == 'DATE_ADD_INTERVAL_YEAR') {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . "DATE(DATE_ADD(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " YEAR)) = CURDATE())");
            } elseif ($dataset['conditions'][$key] == 'DATE_MINUS_INTERVAL_DAY') {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . "DATE(DATE_SUB(" . $field_name . ", INTERVAL " . $dataset['values'][$key]['date_add'] . " DAY)) = CURDATE())");
            } else {
                array_push($sql_conditions, $dataset['subconditions'][$key] . " (" . stringify_date_condition(
                    $field_name,
                    $dataset['conditions'][$key],
                    reformat_str_date($dataset['values'][$key]['date_single'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')
                ) . ")");
            }
        }

        return $sql_conditions;
    }

    public function workflow_builder_view($workflow_builder_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        if (empty($workflow_builder_id)) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        $workflow_builder = $this->workflow_builder_model->getById($workflow_builder_id);
        if (!$workflow_builder) {
            redirect(base_url() . "workflow-builder", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/steps/jquery.steps.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
            asset_url() . 'css/plugins/clockpicker/clockpicker.css',
            asset_url() . 'css/plugins/iCheck/line/line.css',
            asset_url() . 'css/plugins/iCheck/square/blue.css',
            asset_url() . 'css/plugins/switchery/switchery.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/steps/jquery.steps.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/plugins/clockpicker/clockpicker.js',
            asset_url() . 'js/plugins/iCheck/icheck.min.js',
            asset_url() . 'js/plugins/switchery/switchery.js',
            asset_url() . 'js/workflow-builder/workflow-builder-view.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];
        $additional_data['workflow_builder'] = $workflow_builder;
        $additional_data['workflow_builder_json_dataset'] = json_decode($workflow_builder->json_dataset, true);
        $additional_data['partner_data'] = $this->partner_data;
        $additional_data['partner_super_agent'] = $this->partner_super_agent;

        // CONDITIONS
        $view_data['condition_allowed_text_fields'] = $this->_conditionAllowedTextFields;
        $view_data['condition_allowed_select_fields'] = $this->_conditionAllowedSelectFields;
        $view_data['condition_allowed_date_fields'] = $this->_conditionAllowedDateFields;
        $view_data['condition_allowed_multi_fields'] = $this->_conditionAllowedMultiFields;

        $tempFields = $additional_data['workflow_builder_json_dataset']['fields'];
        $tempValues = $additional_data['workflow_builder_json_dataset']['values'];
        $valueApplicationStatus = null;
        $selectedFields = [];

        foreach ($tempFields as $tempFieldKey => $tempFieldValue) {
            $selectedFields[] = $tempFieldKey;
            if ($tempFieldValue == 'application_status') {
                $valueApplicationStatus = $tempValues[$tempFieldKey];
            }
        }
        $view_data['applicationStatusSelected'] = $valueApplicationStatus;

        $allowedFields = array_merge($this->_conditionAllowedTextFields, $this->_conditionAllowedSelectFields, $this->_conditionAllowedDateFields, $this->_conditionAllowedMultiFields);
        asort($allowedFields);
        /*
          if (count($selectedFields) > 0) {
          foreach ($selectedFields as $selectedField) {
          unset($allowedFields[$selectedField]);
          }
          }
          if (!in_array('application_status', $selectedFields)) {
          unset($allowedFields['application_status_tag']);
          }
         */
        $additional_data['condition_allowed_fields'] = $allowedFields;

        $filter = [
            'date_deleted_is_empty' => true,
        ];
        $order = [
            'name',
        ];
        $view_data['email_templates'] = $this->workflow_builder_email_template_model->fetch($filter, $order);
        $view_data['sms_templates'] = $this->workflow_builder_sms_template_model->fetch($filter, $order);

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_view', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    /*
     *
     * too many URL parameters, need to change fro GET to POST
     *
     */

    public function ajax_workflow_builder_dt_post_customers()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        // $dt_params = $this->input->get();
        $dt_params = $this->input->post();
        parse_str($dt_params['filter'], $dataset);

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];

        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        $conditions_arr = $this->_generateSqlStatement($dataset, true);

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" ", $conditions_arr) : "";

        $dataset = $this->customers_model->dt_get_wfb_customers_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->customers_model->dt_get_wfb_customers_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];
        $row_ctr = 0;

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = "row" . $row_ctr++;
            $dt_subset['DT_RowAttr'] = ['customer_id' => $subset['customer_id']];

            //override values
            $dt_subset['customer_id'] = '<span class="badge badge-default m-l-xs m-r-xs">' . $dt_subset['customer_id'] . '</span>';

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    /*
     *
     * Email Template
     *
     */

    public function email_template()
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
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/workflow-builder/workflow-builder-email-template.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        //kb explainer?
        $kb_code = 'workflow_builder_email_template';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $sidebar_data = [];
        $sidebar_data['sidebar_body'] = $this->load->view('workflow_builder/section_filter_workflow_builder_email_template', $view_data, true);
        $sidebar_data['sidebar_body'] .= $this->load->view('workflow_builder/section_column_workflow_builder_email_template', $additional_data, true);

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/main_workflow_builder_email_template', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', array_merge($view_data, $sidebar_data));
    }

    public function ajax_dt_get_work_builder_email_template()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->get();
        $dt_params['columnHeader'] = isset($dt_params['columnHeader']) ? $dt_params['columnHeader'] : '';
        parse_str($dt_params['columnHeader'], $dt_params['columnHeader']);

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        array_push($conditions_arr, stringify_condition("date_deleted", QUERY_FILTER_IS_EMPTY, ""));

        if (isset($dt_params['filterNameOperator']) && !empty($dt_params['filterNameOperator']) && isset($dt_params['filterName']) && !empty($dt_params['filterName'])) {
            array_push($conditions_arr, stringify_condition("name", $dt_params['filterNameOperator'], $dt_params['filterName']));
            $filters_count++;
        }

        if (isset($dt_params['filterSubjectOperator']) && !empty($dt_params['filterSubjectOperator']) && isset($dt_params['filterSubject']) && !empty($dt_params['filterSubject'])) {
            array_push($conditions_arr, stringify_condition("subject", $dt_params['filterSubjectOperator'], $dt_params['filterSubject']));
            $filters_count++;
        }

        if (isset($dt_params['filterDateAddedOperator']) && !empty($dt_params['filterDateAddedOperator'])) {
            if ($dt_params['filterDateAddedOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateAddedFrom']) && !empty($dt_params['filterDateAddedFrom']) && isset($dt_params['filterDateAddedTo']) && !empty($dt_params['filterDateAddedTo'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAddedFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateAddedTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            } elseif (isset($dt_params['filterDateAdded']) && !empty($dt_params['filterDateAdded'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAdded'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            }
        }

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "name" => "name",
                "subject" => "subject",
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->workflow_builder_email_template_model->get_workflow_builder_email_template_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->workflow_builder_email_template_model->get_workflow_builder_email_template_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = "workflow_builder_email_template" . $subset['id'];
            $dt_subset['DT_RowClass'] = "app-summary-row";
            $dt_subset['DT_RowAttr'] = ['attr-workflow-builder-email-template' => $subset['id']];

            $dt_subset['actions'] = $this->load->view('workflow_builder/section_workflow_builder_email_template_dt_actions', $dt_subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_set_workflow_builder_email_template_visible_cols()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->post();

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options['partner_workflow_builder_email_template_columns'] = array_values($this->input->post('columnHeader'));
        $this->session->utilihub_hub_global_view_options = $global_options;

        echo json_encode([]);
    }

    public function email_template_add()
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
        $view_data['user_menu'] = "workflow_builder";

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
            asset_url() . 'js/workflow-builder/workflow-builder-email-template-save.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load_add();";

        //kb explainer?
        $kb_code = 'workflow_builder_email_template_add';
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
        $this->load->view('workflow_builder/workflow_builder_email_template_add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function email_template_update($email_template_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        $email_template = $this->workflow_builder_email_template_model->getById($email_template_id);
        if (!$email_template) {
            redirect(base_url() . 'workflow-builder/email-template/', 'refresh');
        }

        if ($email_template->date_deleted) {
            redirect(base_url() . 'workflow-builder/email-template/', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

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
            asset_url() . 'js/workflow-builder/workflow-builder-email-template-save.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load_update();";

        //kb explainer?
        $kb_code = 'workflow_builder_email_template_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];
        $additional_data['email_template'] = $email_template;
        $additional_data['partner_data'] = $this->partner_data;
        $additional_data['partner_super_agent'] = $this->partner_super_agent;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_email_template_update', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_email_template_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $name = isset($dataset['name']) ? trim($dataset['name']) : '';
        $subject = isset($dataset['subject']) ? trim($dataset['subject']) : '';
        $html_template = isset($dataset['html_template']) ? trim($dataset['html_template']) : '';

        if ($name == '') {
            echo json_encode(['successful' => false, 'error' => "Required field name."]);
            return;
        }

        if ($subject == '') {
            echo json_encode(['successful' => false, 'error' => "Required field subject."]);
            return;
        }

        if (isset($dataset['email_template_id']) && !empty($dataset['email_template_id'])) {
            //UPDATE
            //no need to check if there are seats left
            $email_template = $this->workflow_builder_email_template_model->getById($dataset['email_template_id']);
            if (!$email_template) {
                echo json_encode(['successful' => false, 'error' => "Email template does not exists."]);
                return;
            }

            //START
            $this->db->trans_begin();

            $update_data = [];
            $update_data['id'] = $email_template->id;
            $update_data['name'] = $name;
            $update_data['subject'] = $subject;
            $update_data['html_template'] = $html_template;
            $email_template_id = $this->workflow_builder_email_template_model->save($update_data);
            if (!$email_template_id) {
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
            //START
            $this->db->trans_begin();

            $new_data = [];
            $new_data['name'] = $name;
            $new_data['subject'] = $subject;
            $new_data['html_template'] = $html_template;

            $email_template_id = $this->workflow_builder_email_template_model->save($new_data);
            if (!$email_template_id) {
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

    public function email_template_view($email_template_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        $email_template = $this->workflow_builder_email_template_model->getById($email_template_id);
        if (!$email_template) {
            redirect(base_url() . 'workflow-builder/email-template/', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

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
            asset_url() . 'js/workflow-builder/workflow-builder-email-template-view.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];
        $additional_data['email_template'] = $email_template;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_email_template_view', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_email_template_delete()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $email_template_id = isset($dataset['email_template_id']) ? $dataset['email_template_id'] : null;

        if (!$email_template_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid Email Template"]);
            return;
        }

        $email_template = $this->workflow_builder_email_template_model->getById($email_template_id);
        if (!$email_template) {
            echo json_encode(['successful' => false, 'error' => "Invalid Email Template"]);
            return;
        }

        //START
        $this->db->trans_begin();

        $update_data = [];
        $update_data['id'] = $email_template->id;
        $update_data['date_deleted'] = date('Y-m-d H:i:s');
        $email_template_id = $this->workflow_builder_email_template_model->save($update_data);
        if (!$email_template_id) {
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

        echo json_encode(['successful' => true, 'message' => "Email template deleted."]);
    }

    /*
     *
     * SMS Template
     *
     */

    public function sms_template()
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
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css',
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
            asset_url() . 'js/workflow-builder/workflow-builder-sms-template.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        //kb explainer?
        $kb_code = 'workflow_builder_sms_template';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        // Overview defaults
        $view_data['saved_filter'] = [];
        $global_options = $this->session->utilihub_hub_global_view_options;

        if (!isset($global_options['partner_workflow_builder_sms_template_columns'])) {
            $global_options['partner_workflow_builder_sms_template_columns'] = ["0", "1", "2", "3"];
        }

        if (!empty($this->partner_super_agent['overview_defaults'])) {
            $overviewDefaults = json_decode($this->partner_super_agent['overview_defaults'], true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $view_data['saved_filter'] = isset($overviewDefaults['partner_workflow_builder_sms_template_overview']) ? $overviewDefaults['partner_workflow_builder_sms_template_overview'] : '';

                // columns display
                if (isset($overviewDefaults['partner_workflow_builder_sms_template_overview']['columnHeader']) && is_array($overviewDefaults['partner_workflow_builder_sms_template_overview']['columnHeader'])) {
                    $global_options["partner_workflow_builder_sms_template_columns"] = $overviewDefaults['partner_workflow_builder_sms_template_overview']['columnHeader'];
                }
            }
        }
        $this->session->utilihub_hub_global_view_options = $global_options;

        $additional_data = [];
        $additional_data['partner_data'] = $this->partner_data;
        $additional_data['partner_super_agent'] = $this->partner_super_agent;

        $sidebar_data = [];
        $sidebar_data['sidebar_body'] = '';
        $sidebar_data['sidebar_body'] .= '';

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/main_workflow_builder_sms_template', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', array_merge($view_data, $sidebar_data));
    }

    public function ajax_manager_dt_get_work_builder_sms_template()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->get();
        $dt_params['columnHeader'] = isset($dt_params['columnHeader']) ? $dt_params['columnHeader'] : '';
        parse_str($dt_params['columnHeader'], $dt_params['columnHeader']);

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $filters_count = 0;
        $conditions_arr = [];

        array_push($conditions_arr, stringify_condition("date_deleted", QUERY_FILTER_IS_EMPTY, ""));

        if (isset($dt_params['filterNameOperator']) && !empty($dt_params['filterNameOperator']) && isset($dt_params['filterName']) && !empty($dt_params['filterName'])) {
            array_push($conditions_arr, stringify_condition("name", $dt_params['filterNameOperator'], $dt_params['filterName']));
            $filters_count++;
        }

        if (isset($dt_params['filterDateAddedOperator']) && !empty($dt_params['filterDateAddedOperator'])) {
            if ($dt_params['filterDateAddedOperator'] == QUERY_FILTER_IS_BETWEEN && isset($dt_params['filterDateAddedFrom']) && !empty($dt_params['filterDateAddedFrom']) && isset($dt_params['filterDateAddedTo']) && !empty($dt_params['filterDateAddedTo'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAddedFrom'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d'), reformat_str_date($dt_params['filterDateAddedTo'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            } elseif (isset($dt_params['filterDateAdded']) && !empty($dt_params['filterDateAdded'])) {
                array_push($conditions_arr, stringify_date_condition("date_added", $dt_params['filterDateAddedOperator'], reformat_str_date($dt_params['filterDateAdded'], $this->config->item('mm8_php_default_date_format'), 'Y-m-d')));
                $filters_count++;
            }
        }

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "name" => "name",
                "template" => "template",
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->workflow_builder_sms_template_model->get_workflow_builder_sms_template_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->workflow_builder_sms_template_model->get_workflow_builder_sms_template_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['filtersCount'] = $filters_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;
            $dt_subset['DT_RowId'] = "workflow_builder_sms_template" . $subset['id'];
            $dt_subset['DT_RowClass'] = "app-summary-row";
            $dt_subset['DT_RowAttr'] = ['attr-workflow-builder-sms-template' => $subset['id']];

            $dt_subset['actions'] = $this->load->view('workflow_builder/section_workflow_builder_sms_template_dt_actions', $dt_subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_set_workflow_builder_sms_template_visible_cols()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        $dt_params = $this->input->post();

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options['partner_workflow_builder_sms_template_columns'] = array_values($this->input->post('columnHeader'));
        $this->session->utilihub_hub_global_view_options = $global_options;

        echo json_encode([]);
    }

    public function sms_template_add()
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
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/workflow-builder/workflow-builder-sms-template-save.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load_add();";

        //kb explainer?
        $kb_code = 'workflow_builder_sms_template_add';
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
        $this->load->view('workflow_builder/workflow_builder_sms_template_add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function sms_template_update($sms_template_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        $sms_template = $this->workflow_builder_sms_template_model->getById($sms_template_id);
        if (!$sms_template) {
            redirect(base_url() . 'workflow-builder/sms-template/', 'refresh');
        }

        if ($sms_template->date_deleted) {
            redirect(base_url() . 'workflow-builder/sms-template/', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/workflow-builder/workflow-builder-sms-template-save.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load_update()";

        //kb explainer?
        $kb_code = 'workflow_builder_sms_template_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];
        $additional_data['sms_template'] = $sms_template;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_sms_template_update', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_sms_template_save()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $name = isset($dataset['name']) ? trim($dataset['name']) : '';
        $template = isset($dataset['template']) ? trim($dataset['template']) : '';

        if ($name == '') {
            echo json_encode(['successful' => false, 'error' => "Required field name."]);
            return;
        }

        if ($template == '') {
            echo json_encode(['successful' => false, 'error' => "Required field template."]);
            return;
        }

        if (isset($dataset['sms_template_id']) && !empty($dataset['sms_template_id'])) {
            //UPDATE
            $sms_template = $this->workflow_builder_sms_template_model->getById($dataset['sms_template_id']);
            if (!$sms_template) {
                echo json_encode(['successful' => false, 'error' => "SMS template does not exists."]);
                return;
            }

            //START
            $this->db->trans_begin();

            $update_data = [];
            $update_data['id'] = $sms_template->id;
            $update_data['name'] = $name;
            $update_data['template'] = $template;

            $sms_template_id = $this->workflow_builder_sms_template_model->save($update_data);
            if (!$sms_template_id) {
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
            //START
            $this->db->trans_begin();

            $new_data = [];
            $new_data['name'] = $name;
            $new_data['template'] = $template;

            $sms_template_id = $this->workflow_builder_sms_template_model->save($new_data);
            if (!$sms_template_id) {
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

    public function sms_template_view($sms_template_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        $sms_template = $this->workflow_builder_sms_template_model->getById($sms_template_id);
        if (!$sms_template) {
            redirect(base_url() . 'workflow-builder/sms-template/', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "workflow_builder";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/workflow-builder/workflow-builder-sms-template-view.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];
        $additional_data['sms_template'] = $sms_template;
        $additional_data['partner_data'] = $this->partner_data;
        $additional_data['partner_super_agent'] = $this->partner_super_agent;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('workflow_builder/workflow_builder_sms_template_view', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_sms_template_delete()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || $this->session->utilihub_hub_target_role != $this->active_role) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $sms_template_id = isset($dataset['sms_template_id']) ? $dataset['sms_template_id'] : null;

        if (!$sms_template_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid SMS Template"]);
            return;
        }

        $sms_template = $this->workflow_builder_sms_template_model->getById($sms_template_id);
        if (!$sms_template) {
            echo json_encode(['successful' => false, 'error' => "Invalid SMS Template"]);
            return;
        }

        //START
        $this->db->trans_begin();

        $update_data = [];
        $update_data['id'] = $sms_template->id;
        $update_data['date_deleted'] = date('Y-m-d H:i:s');
        $sms_template_id = $this->workflow_builder_sms_template_model->save($update_data);
        if (!$sms_template_id) {
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

        echo json_encode(['successful' => true, 'message' => "SMS template deleted."]);
    }
}
