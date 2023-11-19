<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function validate_access($is_landing_page = false)
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }
    }

    public function index()
    {
        $this->validate_access();

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "manager_dashboard";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [ asset_url() . 'css/plugins/datapicker/bootstrap-datepicker3.css',];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/chartJs/3.8.0/Chart.min.js',
            asset_url() . 'js/plugins/daterangepicker/2.1.25/moment.min.js',
            asset_url() . 'js/plugins/datapicker/1.7.0/bootstrap-datepicker.js',
            asset_url() . 'js/dashboard/index.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        $additional_data = [];

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $additional_data['reference_date_start'] = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $additional_data['reference_date_end'] = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('dashboard/index', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    

    public function ajax_metrics()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        $filterStart = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterStart'])->format("Y-m-d");
        $filterEnd = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterEnd'])->format("Y-m-d");

        // Open Tickets
        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_OPEN,
            'date_deleted_is_null' => true,
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        if (!empty($filterUserType)) {
            $filter['user_type'] = $filterUserType;
        }
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $countOpenTickets = $this->tickets_model->getCount($filter);

        // Resolved Tickets
        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_RESOLVED,
            'date_deleted_is_null' => true,
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $countResolvedTickets = $this->tickets_model->getCount($filter);

        // Re-open Tickets
        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_REOPEN,
            'date_deleted_is_null' => true,
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $countReOpenTickets = $this->tickets_model->getCount($filter);

        // Closed Tickets
        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_CLOSED,
            'date_deleted_is_null' => true,
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $countClosedTickets = $this->tickets_model->getCount($filter);

        echo json_encode([
            'successful' => true,
            'countOpenTickets' => $countOpenTickets,
            'countResolvedTickets' => $countResolvedTickets,
            'countReOpenTickets' => $countReOpenTickets,
            'countClosedTickets' => $countClosedTickets,
        ]);
    }

    public function ajax_pie_chart()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        $filterStart = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterStart'])->format("Y-m-d");
        $filterEnd = date_create_from_format($this->config->item('mm8_php_default_date_format'), $dataset['filterEnd'])->format("Y-m-d");

        $data = [
            CONNECT_SD_TICKET_STATUS_OPEN => 0,
            // CONNECT_SD_TICKET_STATUS_PENDING => 0,
            CONNECT_SD_TICKET_STATUS_RESOLVED => 0,
            CONNECT_SD_TICKET_STATUS_CLOSED => 0,
            CONNECT_SD_TICKET_STATUS_REOPEN => 0,
        ];

        // Open Tickets
        $filter = [
            'date_deleted_is_null' => true,
            'date_added_between' => [
                'start_date' => $filterStart,
                'end_date' => $filterEnd,
            ],
        ];
        if (!empty($filterUserType)) {
            $filter['user_type'] = $filterUserType;
        }
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $countTicketPerStatus = $this->tickets_model->getTicketsGroupByStatus($filter);
        if (count($countTicketPerStatus) > 0) {
            foreach ($countTicketPerStatus as $row) {
                if (isset($data[$row->status])) {
                    $data[$row->status] = $row->count_id;
                }
            }
        }

        $data = [
            intval($data[CONNECT_SD_TICKET_STATUS_OPEN]),
            intval($data[CONNECT_SD_TICKET_STATUS_RESOLVED]),
            intval($data[CONNECT_SD_TICKET_STATUS_REOPEN]),
            intval($data[CONNECT_SD_TICKET_STATUS_CLOSED]),
        ];

        echo json_encode([
            'successful' => true,
            'data' => $data,
        ]);
    }

    public function ajax_bar_chart()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $dateFrom = new DateTime('7 days ago');
        $dateTo = new DateTime();

        $reference_date_start = $dateFrom->format($this->config->item('mm8_php_default_date_format'));
        $reference_date_end = $dateTo->format($this->config->item('mm8_php_default_date_format'));

        $dataset = $this->input->post();
        $filterStart = isset($dataset['filterStart']) ? $dataset['filterStart'] : $reference_date_start;
        $filterEnd = isset($dataset['filterEnd']) ? $dataset['filterEnd'] : $reference_date_end;
        $filterUserType = isset($dataset['filterUserType']) ? $dataset['filterUserType'] : null;
        $filterApp = isset($dataset['filterApp']) ? $dataset['filterApp'] : null;

        $table = "tbl_tickets";
        $date_type = 'date_added';

        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_OPEN,
            'date_deleted_is_null' => true,
        ];
        if (!empty($filterUserType)) {
            $filter['user_type'] = $filterUserType;
        }
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $dataOpenTickets = $this->tickets_model->barChart($table, $date_type, $filterStart, $filterEnd, $filter);

        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_RESOLVED,
            'date_deleted_is_null' => true,
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $dataResolvedTickets = $this->tickets_model->barChart($table, $date_type, $filterStart, $filterEnd, $filter);

        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_REOPEN,
            'date_deleted_is_null' => true,
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $dataReopenTickets = $this->tickets_model->barChart($table, $date_type, $filterStart, $filterEnd, $filter);

        $filter = [
            'status' => CONNECT_SD_TICKET_STATUS_CLOSED,
            'date_deleted_is_null' => true,
        ];
        if (!empty($filterApp)) {
            $filter['app'] = $filterApp;
        }
        $dataClosedTickets = $this->tickets_model->barChart($table, $date_type, $filterStart, $filterEnd, $filter);
       
        echo json_encode([
            'successful' => true,
            'labels' => $dataOpenTickets['xticks'],
            'dataOpenTickets' => $dataOpenTickets['data'],
            'dataResolvedTickets' => $dataResolvedTickets['data'],
            'dataReopenTickets' => $dataReopenTickets['data'],
            'dataClosedTickets' => $dataClosedTickets['data'],
        ]);
    }
}
