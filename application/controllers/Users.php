<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
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
            asset_url() . 'js/users/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'users';
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
        $this->load->view('users/index', $view_data);
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_users()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dt_search_columns = [
            "u_code" => "tbl_users.u_code",
            "first_name" => "tbl_users.first_name",
            "last_name" => "tbl_users.last_name",
            "email" => "tbl_users.email",
            "mobile_phone" => "tbl_users.mobile_phone",
            "position" => "tbl_users.position",
            "devices" => "devices",
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

        $dataset = $this->users_model->dt_get_users($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->users_model->dt_get_users_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;

            $dt_subset['actions'] = $this->load->view('users/section_dt_actions', $subset, true);

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function add()
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
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/users/user.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'user_add';
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
        $this->load->view('users/add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function update($user_id = null)
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        $user = $this->users_model->getById($user_id);
        if (!$user) {
            redirect(base_url() . "users", 'refresh');
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
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/users/user.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'user_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['user'] = $user;

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('users/update', array_merge($view_data, $additional_data));
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
        $first_name = isset($dataset['first_name']) ? trim($dataset['first_name']) : '';
        $last_name = isset($dataset['last_name']) ? trim($dataset['last_name']) : '';
        $email = isset($dataset['email']) ? trim($dataset['email']) : '';
        $mobile_phone = isset($dataset['mobile_phone']) ? trim($dataset['mobile_phone']) : '';
        $position = isset($dataset['position']) ? trim($dataset['position']) : '';
        $devices = isset($dataset['devices']) ? $dataset['devices'] : [];

        if ($first_name == '') {
            echo json_encode(['successful' => false, 'error' => "Required field First Name."]);
            return;
        }

        if ($last_name == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Last Name."]);
            return;
        }

        if ($email == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Email."]);
            return;
        }

        if ($mobile_phone == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Mobile Phone."]);
            return;
        }

        if ($position == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Position."]);
            return;
        }

        if (isset($dataset['user_id']) && !empty($dataset['user_id'])) {
            //UPDATE
            //no need to check if there are seats left
            $exitingUser = $this->users_model->getById($dataset['user_id']);
            if (!$exitingUser) {
                echo json_encode(['successful' => false, 'error' => "User does not exists."]);
                return;
            }

            $filter = [
                'email' => $email,
                'id_not' => $exitingUser->id,
            ];
            $duplicateEmail = $this->users_model->fetch($filter);
            if ($duplicateEmail) {
                echo json_encode(['successful' => false, 'error' => 'Email already exist.']);
                return;
            }

            //START
            $this->db->trans_begin();

            $data = [];
            $data['id'] = $exitingUser->id;
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = $mobile_phone;
            $data['position'] = $position;

            $user_id = $this->users_model->save($data);
            if (!$user_id) {
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
            
            $exiting = $this->users_model->getByEmail($email);
            if ($exiting) {
                echo json_encode(['successful' => false, 'error' => 'Email already exist.']);
                return;
            }
    
            //START
            $this->db->trans_begin();

            $data = [];
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = $mobile_phone;
            $data['position'] = $position;
            $data['role'] = 1;

            $user_id = $this->users_model->save($data);
            if (!$user_id) {
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

    public function ajax_delete()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();
        $user_id = isset($dataset['user_id']) ? $dataset['user_id'] : null;

        if (!$user_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid User"]);
            return;
        }

        $user = $this->users_model->getById($user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        //START
        $this->db->trans_begin();

        $this->user_pi_device_notification_model->deleteByUserId($user_id);

        if (!$this->users_model->delete($user->id)) {
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

        echo json_encode(['successful' => true, 'message' => "User deleted."]);
    }
}
