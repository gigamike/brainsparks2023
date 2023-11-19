<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('customers_model');
        $this->load->model('invoices_model');
        $this->load->model('invoice_items_model');
        $this->load->model('products_model');
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            return false;
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
        $view_data['user_menu'] = "customers";

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
            asset_url() . 'js/customers/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'merchants';
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
        $this->load->view('customers/index', $view_data);
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_customers()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dt_search_columns = [
            "u_code" => "tbl_customers.u_code",
            "first_name" => "tbl_customers.first_name",
            "last_name" => "tbl_customer_regions.last_name",
            "email" => "tbl_customers.email",
            "mobile_phone" => "tbl_customers.mobile_phone",
            "date_of_birth" => "tbl_customers.date_of_birth",
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

        $dataset = $this->customers_model->dt_get_customers($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->customers_model->dt_get_customers_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;

            $dt_subset['actions'] = $this->load->view('customers/section_dt_actions', $subset, true);

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
        $view_data['user_menu'] = "customers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/customers/customer.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'customer_add';
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
        $this->load->view('customers/add', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function view($customer_id = null)
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        $customer = $this->customers_model->getById($customer_id);
        if (!$customer) {
            redirect(base_url() . "customers", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "customers";

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
            asset_url() . 'js/plugins/clipboard/clipboard.min.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/customers/view.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'customer_view';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['customer'] = $customer;

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('customers/view', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_invoices()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dt_search_columns = [
            "invoice_u_code" => "invoice_u_code",
            "date_purchased" => "date_purchased",
            "product_u_code" => "product_u_code",
            "product_name" => "product_name",
            "price" => "price",
            "quantity" => "quantity",
            "date_added" => "date_added",
        ];

        $dt_params = $this->input->get();

        $customer_id = isset($dt_params['customer_id']) ? trim($dt_params['customer_id']) : null;

        $customer = $this->customers_model->getById($customer_id);
        if (!$customer) {
            redirect(base_url() . "customers", 'refresh');
        }

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        $conditions_arr = [];

        array_push($conditions_arr, "tbl_invoices.customer_id = " . $this->db->escape($customer->id));

        if (isset($dt_params['searchText']) && $dt_params['searchText'] != "") {
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

        $dataset = $this->invoices_model->dt_get_invoices($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->invoices_model->dt_get_invoices_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            $dt_subset = $subset;

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function update($customer_id = null)
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        $customer = $this->customers_model->getById($customer_id);
        if (!$customer) {
            redirect(base_url() . "customers", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "customers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/customers/customer.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'customer_update';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $view_data['customer'] = $customer;

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('customers/update', array_merge($view_data, $additional_data));
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
        $date_of_birth = isset($dataset['date_of_birth']) ? trim($dataset['date_of_birth']) : '';
        
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
        }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['successful' => false, 'error' => "Invalid Email."]);
            return;
        }

        if ($mobile_phone == '') {
            echo json_encode(['successful' => false, 'error' => "Required field Mobile Phone."]);
            return;
        }

        if (isset($dataset['customer_id']) && !empty($dataset['customer_id'])) {
            //UPDATE
            $exitingCustomer = $this->customers_model->getById($dataset['customer_id']);
            if (!$exitingCustomer) {
                echo json_encode(['successful' => false, 'error' => "Customer does not exists."]);
                return;
            }

            $filter = [
                'email' => $email,
                'id_not' => $exitingCustomer->id,
            ];
            $duplicateEmail = $this->customers_model->fetch($filter);
            if ($duplicateEmail) {
                echo json_encode(['successful' => false, 'error' => 'Email already exist.']);
                return;
            }

            $filter = [
                'mobile_phone' => $mobile_phone,
                'id_not' => $exitingCustomer->id,
            ];
            $duplicateMobilePhone = $this->customers_model->fetch($filter);
            if ($duplicateMobilePhone) {
                echo json_encode(['successful' => false, 'error' => 'Mobile phone already exist.']);
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
            $data['date_of_birth'] = $date_of_birth;

            $customer_id = $this->customers_model->save($data);
            if (!$customer_id) {
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
            $emailExists = $this->customers_model->getByEmail($email);
            if($emailExists){
                echo json_encode(['successful' => false, 'error' => 'Customer email already exists']);
                return;
            }

            $mobilePhoneExists = $this->customers_model->getByMobilePhone($mobile_phone);
            if($mobilePhoneExists){
                echo json_encode(['successful' => false, 'error' => 'Customer mobile phone already exists']);
                return;
            }
           
            //START
            $this->db->trans_begin();

            $data = [];
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = empty($mobile_phone) ? null : $mobile_phone;
            $data['date_of_birth'] = $date_of_birth;

            $customer_id = $this->customers_model->save($data);
            if (!$customer_id) {
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
        $customer_id = isset($dataset['customer_id']) ? $dataset['customer_id'] : null;

        if (!$customer_id) {
            echo json_encode(['successful' => false, 'error' => "Invalid Plate Number"]);
            return;
        }

        $customer = $this->customers_model->getById($customer_id);
        if (!$customer) {
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        //START
        $this->db->trans_begin();

        if (!$this->customers_model->delete($customer->id)) {
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

        echo json_encode(['successful' => true, 'message' => "Plate number deleted."]);
    }

    public function add_import()
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
        $view_data['user_menu'] = "customers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/customers/customer-import.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'customer_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $view_data['text_placeholder'] = "\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"\n\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"\n\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"\n";
        $view_data['text_format'] = "next line separated \"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"";
        $view_data['csv_format'] = "\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"<br>\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"<br>\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\"<br>";
        
        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('customers/add_import', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_bulk_import_save()
    {
        //update time outs since uploads may take time to complete
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //lets check if uploaded file is csv
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $file_mime_type = mime_content_type($_FILES['csv']['tmp_name']);

            $allowedFileTypes = ['application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv'];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                echo json_encode(['successful' => false, 'error' => "Invalid file/mime type. Make sure your file is a valid CSV file"]);
                return;
            }
        }

        //form data
        $dataset = $this->input->post();
        $text = isset($dataset['text']) ? trim($dataset['text']) : '';
        
        $inserted = 0;

        if ($text != '') {
            $csvRows = [];
            $string = explode("\n", $text);
            if (count($string) > 0) {
                foreach ($string as $stringRow) {
                    $csv = str_getcsv($stringRow);
                    if (count($csv) > 0) {
                        $csvRows[] = $csv;
                    }
                }
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importCustomers($csvRows);
            }
        }
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $csvRows = [];
            if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row = [];
                    $num = count($data);
                    for ($c = 0; $c < $num; $c++) {
                        $row[] = $data[$c];
                    }
                    $csvRows[] = $row;
                    unset($row);
                }
                fclose($handle);
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importCustomers($csvRows);
            }
        }

        echo json_encode(['successful' => true, 'message' => "Bulk import successful. Total Prospect inserted " . $inserted]);
    }

    private function _importCustomers($customers)
    {
        $inserted = 0;

        $this->db->trans_begin();

        $rowCtr = 0;
        foreach ($customers as $customer) {
            $rowCtr++;

            if($rowCtr == 1){
                continue;
            }

            $first_name = isset($customer[0]) ? trim($customer[0]) : null;
            $last_name = isset($customer[1]) ? trim($customer[1]) : null;
            $email = isset($customer[2]) ? trim($customer[2]) : null;
            $mobile_phone = isset($customer[3]) ? trim($customer[3]) : null;

            if ($first_name == '') {
                continue;
            }

            if ($last_name == '') {
                continue;
            }

            if ($email == '') {
                continue;
            }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($mobile_phone == '') {
                continue;
            }

            $emailExists = $this->customers_model->getByEmail($email);
            if($emailExists){
                continue;
            }

            $mobilePhoneExists = $this->customers_model->getByMobilePhone($mobile_phone);
            if($mobilePhoneExists){
                continue;
            }

            $data = [];
            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = empty($mobile_phone) ? null : $mobile_phone;

            $customer_id = $this->customers_model->save($data);
            if (!$customer_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $inserted++;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        $this->db->trans_commit();

        return $inserted;
    }

    public function add_import2()
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
        $view_data['user_menu'] = "customers";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/datapicker/datepicker3.css',
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/bootstrap-datepicker.js',
            asset_url() . 'js/customers/customer-import2.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'customer_add';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $view_data['text_placeholder'] = "\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"\n\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"\n\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"\n";
        $view_data['text_format'] = "next line separated \"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Product Code\",\"Quantity\",\"Date Purchsed\"";
        $view_data['csv_format'] = "\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"<br>\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"<br>\"First Name\",\"Last Name\",\"Email\",\"Mobile Phone\",\"Birth Date\",\"Invoice Code\",\"Product Code\",\"Quantity\",\"Date Purchsed\"<br>";
        
        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('customers/add_import2', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_bulk_import_save2()
    {
        //update time outs since uploads may take time to complete
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //lets check if uploaded file is csv
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $file_mime_type = mime_content_type($_FILES['csv']['tmp_name']);

            $allowedFileTypes = ['application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv'];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                echo json_encode(['successful' => false, 'error' => "Invalid file/mime type. Make sure your file is a valid CSV file"]);
                return;
            }
        }

        //form data
        $dataset = $this->input->post();
        $text = isset($dataset['text']) ? trim($dataset['text']) : '';
        
        $inserted = 0;

        if ($text != '') {
            $csvRows = [];
            $string = explode("\n", $text);
            if (count($string) > 0) {
                foreach ($string as $stringRow) {
                    $csv = str_getcsv($stringRow);
                    if (count($csv) > 0) {
                        $csvRows[] = $csv;
                    }
                }
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importCustomers2($csvRows);
            }
        }
        if (isset($_FILES['csv']['tmp_name']) && $_FILES['csv']['tmp_name'] != "" && file_exists($_FILES['csv']['tmp_name'])) {
            $csvRows = [];
            if (($handle = fopen($_FILES['csv']['tmp_name'], "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row = [];
                    $num = count($data);
                    for ($c = 0; $c < $num; $c++) {
                        $row[] = $data[$c];
                    }
                    $csvRows[] = $row;
                    unset($row);
                }
                fclose($handle);
            }
            if (count($csvRows) > 0) {
                $inserted = $this->_importCustomers2($csvRows);
            }
        }

        echo json_encode(['successful' => true, 'message' => "Bulk import successful. Total Prospect inserted " . $inserted]);
    }

    private function _importCustomers2($customers)
    {
        $inserted = 0;

        $this->db->trans_begin();

        $rowCtr = 0;
        foreach ($customers as $customer) {
            $rowCtr++;

            if($rowCtr == 1){
                continue;
            }

            $first_name = isset($customer[0]) ? trim($customer[0]) : null;
            $last_name = isset($customer[1]) ? trim($customer[1]) : null;
            $email = isset($customer[2]) ? trim($customer[2]) : null;
            $mobile_phone = isset($customer[3]) ? trim($customer[3]) : null;
            $date_of_birth = isset($customer[4]) ? trim($customer[4]) : null;
            $invoice_code = isset($customer[5]) ? trim($customer[5]) : null;
            $product_code = isset($customer[6]) ? trim($customer[6]) : null;
            $quantity = isset($customer[7]) ? trim($customer[7]) : null;
            $date_purchased = isset($customer[8]) ? trim($customer[8]) : null;

            if ($first_name == '') {
                continue;
            }

            if ($last_name == '') {
                continue;
            }

            if ($email == '') {
                continue;
            }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($mobile_phone == '') {
                continue;
            }

            $data = [];
            $emailExists = $this->customers_model->getByEmail($email);
            if($emailExists){
                $data['id'] = $emailExists->id;
            }

            $data['first_name'] = $first_name;
            $data['last_name'] = $last_name;
            $data['email'] = $email;
            $data['mobile_phone'] = empty($mobile_phone) ? null : $mobile_phone;
            $data['date_of_birth'] = empty($date_of_birth) ? null : $date_of_birth;
            $customer_id = $this->customers_model->save($data);
            if (!$customer_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $data = [];
            $invoice = $this->invoices_model->getByUCode($invoice_code);
            if($invoice){
                $data['id'] = $invoice->id;
            }
            $data['customer_id'] = $customer_id;
            $data['u_code'] = $invoice_code;
            $data['date_added'] = $date_purchased;
            $invoice_id = $this->invoices_model->save($data);
            if (!$invoice_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $product = $this->products_model->getByUCode($product_code);
            if(!$product){
                continue;
            }

            $data = [];
            $data['invoice_id'] = $invoice_id;
            $data['product_id'] = $product->id;
            $data['quantity'] = $quantity;
            $data['price'] = $product->price;
            $invoice_item_id = $this->invoice_items_model->save($data);
            if (!$invoice_item_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_502]);
                return;
            }

            $inserted++;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_502]);
            return;
        }

        $this->db->trans_commit();

        return $inserted;
    }

    public function ajax_get_products()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        //form data
        $dataset = $this->input->post();

        $view_data = [];

        $filter = [

        ];
        $order = [
            'RAND()',
        ];
        $view_data['products'] = $this->products_model->getProducts($filter, $order);
        $html = $this->load->view('customers/section_products', $view_data, true);

        echo json_encode([
            'successful' => true,
            'html' => $html,
        ]);
        return;
    }
}
