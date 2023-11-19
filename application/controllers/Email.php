<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email extends CI_Controller
{
    protected $partner_data = null;
    protected $agent_data = null;
    protected $prefix = null;
    protected $prefix_lookup = [USER_ADMIN => "partner"];
    protected $allowed_roles = [USER_ADMIN => 1];

    public function __construct()
    {
        parent::__construct();

        $this->load->model('partner_model');
        $this->load->model('customer_model');
        $this->load->model('log_email_model');
        $this->load->model('communications_model');
        $this->load->model('application_model');
        $this->load->model('backend_google_authenticate_model');
        $this->load->model('partner_agents_email_address_model');
        $this->load->model('partner_dashboard_model');
        $this->load->model('short_url_model');
        $this->load->model('log_model');

        $this->load->helper('time_elapsed');

        $this->load->library('email_library');
        $this->load->library('pagination');
        $this->load->library('account_manager_library');

        //ses
        $this->load->library('aws_ses_library');

        //s3
        $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_private_bucket')], 'aws_s3_library_private');

        $this->load->library('connect_sd_library');
        $this->connect_sd_library->getSessionChatChannel(CONNECT_SD_APP_HUB); // Connect SD is on all pages, check active/inactive chat channel
    }

    protected function validate_access($show_when_disabled = false)
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        if (!isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            redirect(base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_target_role], 'refresh');
        }

        //user access
        $this->session->utilihub_hub_user_access = $this->dashboard_user_model->get_user_access($this->session->utilihub_hub_user_role, $this->session->utilihub_hub_user_id);

        //set agent data for global access (only if needed)
        if (!empty($this->session->utilihub_hub_active_agent_id)) {
            $this->agent_data = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_active_agent_id);
            if ($this->agent_data === false) {
                //planning to redirect to landing page but may cause an infinite loop!
                redirect('login', 'refresh');
            }
        }

        //set partner data for global access
        $this->partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
        if (count($this->partner_data) <= 0) {
            //planning to redirect to landing page but may cause an infinite loop!
            redirect('login', 'refresh');
        }

        $this->prefix = $this->prefix_lookup[$this->session->utilihub_hub_target_role];

        //partner disabled? agent disabled?
        if (!$show_when_disabled && ((int) $this->partner_data['active'] == STATUS_NG || (!empty($this->agent_data) && (int) $this->agent_data['active'] == STATUS_NG))) {
            //redirect to home
            redirect(base_url() . 'login/home', 'refresh');
        }

        $this->account_manager_library->access_log();
        return true;
    }

    public function thread($log_email_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        if ($this->partner_data['package_type'] != PACKAGE_CONNECTIONS) {
            redirect(base_url() . $this->config->item('hub_landing_page')[$this->active_role], 'refresh');
        }

        if (empty($log_email_id)) {
            redirect(base_url() . $this->prefix . "/email", 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = $this->prefix . "_email";
        $view_data['package_type'] = $this->partner_data['package_type'];
        $view_data['partner_active'] = $this->partner_data['active'];
        $view_data['agent_active'] = $this->agent_data['active'];
        $view_data['prefix'] = $this->prefix;
        $view_data['partner_data'] = $this->partner_data;

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
            asset_url() . 'css/plugins/select2/select2.css',
            asset_url() . 'css/plugins/select2/select2-bootstrap.css'
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/plugins/select2/select2.js',
            asset_url() . 'js/hub-compose-email.js',
            asset_url() . 'js/hub-email-thread.js',
            asset_url() . 'js/hub-csagent-referrals-connections-plus.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init()";

        $additional_data = [];

        $currentLogEmail = $this->log_email_model->getById($log_email_id);
        if (!$currentLogEmail) {
            redirect(base_url() . $this->prefix . "/email", 'refresh');
        }



        $additional_data['current_log_email'] = $currentLogEmail;

        if ($currentLogEmail->application_id) {
            $filterLogEmail = [
                'application_id' => $currentLogEmail->application_id,
            ];

            $application = $this->application_model->get_application_customer_info($currentLogEmail->application_id);
            if (count($application) <= 0 || $application['partner_id'] != $this->session->utilihub_hub_active_partner_id) {
                redirect(base_url() . $this->prefix . "/email", 'refresh');
            }

            $application_id = $application['app_id'];
            $partner_id = $application['partner_id'];

            $additional_data['app_id'] = $application_id;
            $additional_data['customer_code'] = $application['customer_code'];
            $additional_data['reference_code'] = $application['reference_code'];
            $additional_data['full_name'] = $application['full_name'];
            $additional_data['customer_email'] = $application['email'];

            $dataset = $this->application_model->get_application_customer_info($application_id);
        } else {
            // possibly no application in email subject
            $filterLogEmail = [
                'id' => $log_email_id,
            ];

            $application_id = null;
            $partner_id = $currentLogEmail->partner_id;

            $additional_data['app_id'] = $application_id;
            $additional_data['customer_code'] = null;
            $additional_data['reference_code'] = null;
            $additional_data['full_name'] = null;
            $additional_data['customer_email'] = null;

            $dataset = [
                'email' => '',
                'first_name' => '',
                'reference_code' => '',
                'move_in_date' => '',
                'full_name' => '',
                'new_address' => '',
            ];

            // Dropdown Application
            $order_col = "reference_code";
            $order_dir = "ASC";
            $conditions_arr = [];
            array_push($conditions_arr, "tbl_application.partner_id = " . $this->db->escape($this->session->utilihub_hub_active_partner_id));
            $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";
            $additional_data['applications'] = $this->partner_dashboard_model->dashboard_get_applications_summary($order_col, $order_dir, "", "", $condition);
        }

        $additional_data['date_cron_gmail_scrap_last_run'] = $this->_getLastEmailScrap();

        // Get Emails
        $orderLogEmail = [
            'date_processed DESC',
            'id DESC'
        ];
        $emails = $this->log_email_model->fetch($filterLogEmail, $orderLogEmail);
        if (count($emails) > 0) {
            foreach ($emails as $key => $email) {
                $data = [
                    'id' => $email->id,
                    'is_read' => 1,
                ];
                $this->log_email_model->save($data);

                // Plain text removed
                // $emails[$key]->text_contents = str_replace("\\r\\n", "<br/>", $email->text_message);
                // attachments from system is comma separated URLs while in gmail json format
                if (!empty($email->attachment)) {
                    $attachments = json_decode($email->attachment, true);
                    // print_r($attachments);
                    if (json_last_error() == JSON_ERROR_NONE) {
                        if (is_array($attachments) && count($attachments) > 0) {
                            foreach ($attachments as $attachment) {
                                $email->attachments[] = [
                                    'mimeType' => $attachment['mimeType'],
                                    'filename' => $attachment['filename'],
                                    'fileUrl' => $attachment['fileUrl'],
                                ];
                            }
                        }
                    } else {
                        $attachments = explode(",", $email->attachment);
                        if (is_array($attachments) && count($attachments) > 0) {
                            foreach ($attachments as $attachment) {
                                $email->attachments[] = [
                                    'filename' => basename($attachment),
                                    'fileUrl' => $attachment,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $additional_data['emails'] = $emails;

        // get thread started and set the subject
        $subject = "";
        $orderLogEmail = [
            'date_processed',
        ];
        $limit = 1;
        $threadStarter = $this->log_email_model->fetch($filterLogEmail, $orderLogEmail, $limit);
        if (count($threadStarter) > 0) {
            $subject = $threadStarter[0]->subject;
        }

        $partner_data = $this->partner_data;
        $user_data = $this->agent_data;

        $dataset['new_address'] = $this->application_model->address_db_to_string($dataset, 'new');

        $fromEmails = [];
        $fromEmailDefault = null;

        // Default from Partner
        if (!empty($user_data['default_from_email'])) {
            $fromEmailDefault = $user_data['default_from_email'];
        }
        if (!empty($user_data['default_email_thread_from'])) {
            $fromEmailDefault = $user_data['default_email_thread_from'];
        }
        if (!empty($partner_data['default_ops_email'])) {
            $fromEmails[$partner_data['default_ops_email']] = [
                'from_name' => $partner_data['portal_name'],
                'email' => $partner_data['default_ops_email'],
            ];

            if (!empty($fromEmailDefault)) {
                $fromEmailDefault = $partner_data['default_ops_email'];
            }
        }
        if (!empty($partner_data['ops_email'])) {
            $fromEmails[$partner_data['ops_email']] = [
                'from_name' => $partner_data['portal_name'],
                'email' => $partner_data['ops_email'],
            ];
        }
        $filter = [
            'agent_id' => $this->session->utilihub_hub_user_id,
            'verified' => 1,
        ];
        $order = [
            'email_address',
        ];
        $agentFromEmails = $this->partner_agents_email_address_model->fetch($filter, $order);
        if (count($agentFromEmails) > 0) {
            foreach ($agentFromEmails as $row) {
                $fromEmails[$row->email_address] = [
                    'from_name' => $user_data['first_name'] . " at " . $partner_data['portal_name'],
                    'email' => $row->email_address,
                ];
            }
        }
        if (count($fromEmails) > 0) {
            asort($fromEmails);
        }

        $replyToEmails = [];
        $replyToEmailDefault = null;
        if (!empty($user_data['default_reply_to_email'])) {
            $replyToEmailDefault = $user_data['default_reply_to_email'];
        }
        if (!empty($user_data['default_email_thread_reply_to'])) {
            $replyToEmailDefault = $user_data['default_email_thread_reply_to'];
        }
        if (!empty($partner_data['ops_email_reply_to'])) {
            $replyToEmails[] = $partner_data['ops_email_reply_to'];
            if (empty($replyToEmailDefault)) {
                $replyToEmailDefault = $partner_data['ops_email_reply_to'];
            }
        }
        $filter = [
            'agent_id' => $this->session->utilihub_hub_user_id,
            'verified' => 1,
        ];
        $order = [
            'email_address',
        ];
        $agentReplyToEmails = $this->partner_agents_email_address_model->fetch($filter, $order);
        if (count($agentReplyToEmails) > 0) {
            foreach ($agentReplyToEmails as $row) {
                $replyToEmails[] = $row->email_address;
            }
        }
        if (count($replyToEmails) > 0) {
            $replyToEmails = array_unique($replyToEmails);
            sort($replyToEmails);
        }

        $email_signature = $this->email_library->get_partner_agent_email_signature($partner_data['id'], $this->session->utilihub_hub_user_id);

        $additional_data['itool_email_application_id'] = $application_id;
        $additional_data['itool_email_partner_id'] = $partner_id;
        $additional_data['itool_email_user_id'] = $this->session->utilihub_hub_user_id;
        $additional_data['itool_email_from'] = $fromEmails;
        $additional_data['itool_email_from_default'] = $fromEmailDefault;
        $additional_data['itool_email_reply_to'] = $replyToEmails;
        $additional_data['itool_email_reply_to_default'] = $replyToEmailDefault;
        $additional_data['itool_email_to'] = $dataset['email'];
        $additional_data['itool_email_subject'] = $subject;
        $additional_data['itool_email_body'] = $email_signature;
        $additional_data['itool_quicktemplate_data'] = $this->communications_model->get_email_templates_list('instanttool', $partner_id);
        $additional_data['itool_app_details'] = [
            'app_first_name' => $dataset['first_name'],
            'app_partner_hotline' => $partner_data['hotline'],
            'app_ref_code' => $dataset['reference_code'],
            'app_user_name' => $user_data['first_name'],
            'app_portal_name' => $partner_data['portal_name'],
            'app_move_in_date' => $dataset['move_in_date'],
            'app_full_name' => $dataset['full_name'],
            'app_new_address' => $dataset['new_address'],
            'app_partner_name' => $partner_data['name']
        ];

        $users_list = [];
        $additional_data['agent_data'] = $user_data;

        $view_data['users_list'] = $users_list;
        $view_data['current_user_email'] = [$this->session->utilihub_hub_user_profile_email];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('email/thread', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_dt_get_customers_expand_row()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        $customer_id = $this->input->post('customer');
        if (empty($customer_id)) {
            echo json_encode(['successful' => false]);
            return;
        }

        $customer_data = $this->customer_model->get_customer($customer_id);
        if (!$customer_data) {
            echo json_encode(['successful' => false]);
            return;
        }

        if ($this->session->utilihub_hub_target_role == USER_ADMIN) {
            //validate: customer should be from active parnter
            if ($this->session->utilihub_hub_active_partner_id != $customer_data['partner_id']) {
                echo json_encode(['successful' => false]);
                return;
            }
        }

        //applications
        $customer_data['applications'] = $this->customer_model->get_customer_applications($customer_data['id']);

        $html_str = $this->load->view('email/section_modal_customer_row', $customer_data, true);
        echo json_encode(['successful' => true, 'html' => $html_str]);
    }

    public function ajax_submit_instant_tool_email()
    {
        //update time outs since uploads may take time to complete
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        //form data
        $dataset = $this->input->post();
        if (count($dataset) <= 0) {
            echo json_encode(['status' => STATUS_NG, 'error' => ERROR_512]);
            return;
        }


        //update some agent data for tracking email threads
        //START
        $this->db->trans_begin();

        $user_data = [];
        $user_data['default_email_thread_from'] = trim($dataset['itoolEmailFrom']);
        $user_data['default_email_thread_reply_to'] = trim($dataset['itoolEmailReplyTo']);

        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();

        //get cc users email
        $cc_users = '';
        if (isset($dataset['ccUserAssignedFilter']) && is_array($dataset['ccUserAssignedFilter']) && count($dataset['ccUserAssignedFilter']) > 0) {
            $cc_users = implode(",", $dataset['ccUserAssignedFilter']);
        }
        // get bcc users email
        $bcc_users = '';
        if (isset($dataset['bccUserAssignedFilter']) && is_array($dataset['bccUserAssignedFilter']) && count($dataset['bccUserAssignedFilter']) > 0) {
            $bcc_users = implode(",", $dataset['bccUserAssignedFilter']);
        }


        //HAS FILE ATTACHMENT?
        if (isset($_FILES['itoolEmailAttachment']['tmp_name']) && $_FILES['itoolEmailAttachment']['tmp_name'] != "" && file_exists($_FILES['itoolEmailAttachment']['tmp_name'])) {
            $file_mime_type = mime_content_type($_FILES['itoolEmailAttachment']['tmp_name']);

            $allowedFileTypes = [
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/pdf',
                'application/doc',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                echo json_encode(['status' => STATUS_NG, 'error' => "Invalid file type. Make sure the it is either a JPEG, PNG, GIF, PDF, DOC, DOCX, XLS or XLSX."]);
                return;
            }

            //check file size
            if (filesize($_FILES['itoolEmailAttachment']['tmp_name']) > 4000000) {
                echo json_encode(['status' => STATUS_NG, 'error' => "Attachment too large. Make sure the file is not more than 4MB."]);
                return;
            }

            //UPLOAD FILE NOW
            $sub_dir = 'uploads/' . date("Y/m") . '/' . $dataset['itoolEmailApplication'] . $dataset['itoolEmailUser'] . random_string('alnum', 7) . '/';
            $file_dir = FCPATH . $sub_dir;
            if (!file_exists($file_dir)) {
                $oldumask = umask(0);
                mkdir($file_dir, 0775, true);
                umask($oldumask);

                if (!file_exists($file_dir)) {
                    echo json_encode(['status' => false, 'error' => "Internal Error. Error uploading file. Try again."]);
                    return;
                }
            }

            $filename = getRandomAlphaNum() . "." . pathinfo($_FILES['itoolEmailAttachment']['name'], PATHINFO_EXTENSION); //$_FILES['itoolEmailAttachment']['name'];
            if (!move_uploaded_file($_FILES['itoolEmailAttachment']['tmp_name'], $file_dir . $filename) || !file_exists($file_dir . $filename)) {
                echo json_encode(['status' => STATUS_NG, 'error' => "Error attaching file. Try again."]);
                return;
            }

            //SAVE FILE TO S3
            if (ENVIRONMENT == "production") {
                //wait for object to be created in s3
                $file_url = $this->aws_s3_library_private->put_object($file_dir . $filename, $sub_dir . $filename, '', true);
                if ($file_url === false) {
                    echo json_encode(['successful' => false, 'error' => "Error uploading file. Try again."]);
                    return;
                }
            } else {
                $file_url = base_url() . $sub_dir . $filename;
            }
        } else {
            $file_dir = "";
            $filename = "";
            $file_url = "";
        }

        $email_data = $this->email_library->send_customer_email($dataset['itoolEmailPartner'], $dataset['itoolEmailApplication'], $dataset['itoolEmailFromName'], $dataset['itoolEmailFrom'], $dataset['itoolEmailReplyTo'], $dataset['itoolEmailTo'], $cc_users, $bcc_users, $dataset['itoolEmailSubject'], $dataset['itoolEmailMessage'], $file_dir . $filename, true);

        if ($filename != "" && isset($email_data['attachment'])) {
            $email_data['attachment'] = $file_url;
        }

        if (isset($email_data['application_id'])) {
            $application_data = $this->application_model->get_application_customer_info($email_data['application_id']);
            if ($application_data) {
                $email_data['partner_id'] = $application_data['partner_id'];
            }
        }

        //log
        $email_data['processed'] = STATUS_OK;
        $email_data['date_processed'] = $this->database_tz_model->now();
        $this->log_model->log_email($email_data);

        if ((int) $email_data['status'] == STATUS_OK) {
            $this->log_model->log_activity($dataset['itoolEmailApplication'], null, "Email with subject '" . $email_data['subject'] . "' sent", ACTIVITY_COMMS, $dataset['itoolEmailUser'], null, 1);
            $this->log_model->log_activity($dataset['itoolEmailApplication'], null, "Quick email sent to " . $dataset['itoolEmailTo'], ACTIVITY_COMMS, $dataset['itoolEmailUser']);
        } else {
            $this->log_model->log_activity($dataset['itoolEmailApplication'], null, "Quick email with subject '" . $email_data['subject'] . "' failed to send to " . $dataset['itoolEmailTo'], ACTIVITY_COMMS, $dataset['itoolEmailUser']);
        }

        echo json_encode($email_data);
    }

    /*
     *
     * Manually assign Appliction for Email
     * Manager Only
     *
     */

    public function ajax_load_assign_application()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['html' => '']);
            return;
        }

        $id = $this->input->post('id');
        $type = $this->input->post('type');

        if (empty($id) || empty($type)) {
            echo json_encode(['html' => 'Invalid entries']);
            return;
        }

        $view_data = [];

        switch ($type) {
            case 'email':
                $currentLog = $this->log_email_model->getById($id);
                if (!$currentLog) {
                    echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid Email']);
                    return;
                }
                break;
            case 'sms':
                $currentLog = $this->log_sms_model->getById($id);
                if (!$currentLog) {
                    echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid SMS']);
                    return;
                }
                break;
            default:
                echo json_encode(['html' => 'Invalid entries']);
                return;
        }

        $view_data['log'] = $currentLog;
        $view_data['type'] = $type;
        $view_data['user_id'] = $user_profile['id'];
        $view_data['user_role'] = $user_profile['role'];

        $allowedStatuses = [OPEN, QUICK];
        $view_data['application_status_list'] = [];
        foreach ($this->config->item('mm8_application_status') as $status) {
            if (in_array($status, $allowedStatuses)) {
                $view_data['application_status_list'][$status] = $this->config->item('mm8_status_names')[$status];
            }
        }

        $return_data['html'] = $this->load->view('email/show_tool_assign_application_modal', $view_data, true);
        echo json_encode($return_data);
    }

    /*
     *
     * Manually assign Appliction for Email
     * Manager Only
     *
     */

    public function ajax_assign_application()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        $dataset = $this->input->post();
        if (count($dataset) <= 0) {
            echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid entries']);
            return;
        }

        $reference_code = $dataset['reference_code'];
        $log_id = $dataset['log_id'];
        $type = $dataset['type'];

        if (empty($reference_code) || empty($log_id) || empty($type)) {
            echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid entries']);
            return;
        }

        $application = $this->application_model->get_application_from_reference($dataset['reference_code']);
        if (!$application) {
            echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid Application']);
            return;
        }


        $logData = [
            'id' => $log_id,
            'application_id' => $application['id'],
            'partner_id' => $application['partner_id'],
            'application_assign_user_id' => $this->session->mhub_user_id,
            'date_application_assign' => date('Y-m-d H:i:s'),
        ];

        switch ($type) {
            case 'email':
                $currentLog = $this->log_email_model->getById($log_id);
                if (!$currentLog) {
                    echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid Email']);
                    return;
                }

                $this->db->trans_begin();
                if (!$this->log_email_model->save($logData)) {
                    $this->db->trans_rollback();

                    echo json_encode(['successful' => false, 'error' => "Email assign application update failed! (ERROR_502)"]);
                    return;
                }
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => "Email assign application update failed! (ERROR_502)"]);
                    return;
                }
                $this->db->trans_commit();
                break;
            case 'sms':
                $currentLog = $this->log_sms_model->getById($log_id);
                if (!$currentLog) {
                    echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid SMS']);
                    return;
                }

                $this->db->trans_begin();
                if (!$this->log_sms_model->save($logData)) {
                    $this->db->trans_rollback();

                    echo json_encode(['successful' => false, 'error' => "SMS assign application update failed! (ERROR_502)"]);
                    return;
                }
                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => "SMS assign application update failed! (ERROR_502)"]);
                    return;
                }
                $this->db->trans_commit();
                break;
            default:
                echo json_encode(['status' => STATUS_NG, 'error' => 'Invalid entries']);
                return;
        }

        $data['successful'] = true;
        $data['status'] = "Application successfully assign.";

        echo json_encode($data);
    }

    /*
     *
     * Manually assign Appliction for Email
     * Manager Only
     *
     */

    public function ajax_reload_application($rowno = 0)
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        $filterStatus = $this->input->get('filterStatus');

        $data = [];

        //CONDITIONS
        $conditions_arr = [];

        // Dropdown Application
        $order_col = "date_added";
        $order_dir = "DESC";
        $conditions_arr = [];
        array_push($conditions_arr, "tbl_application.partner_id = " . $this->db->escape($this->session->utilihub_hub_active_partner_id));
        if (!empty($filterStatus)) {
            array_push($conditions_arr, "tbl_application.application_status = " . $filterStatus);
        }
        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        // Row per page
        $rowperpage = 5;
        // Row position
        if ($rowno != 0) {
            $rowno = ($rowno - 1) * $rowperpage;
        }
        // All records count
        $allcount = $this->partner_dashboard_model->dashboard_get_applications_count($condition);

        // Get records
        $order_col = 'date_added';
        $order_dir = 'DESC';
        $applications = $this->partner_dashboard_model->dashboard_get_applications_summary($order_col, $order_dir, $rowno, $rowperpage, $condition);

        $config = [];

        // $config['base_url'] = base_url() . "email/ajax-reload-application";
        $config['use_page_numbers'] = true;
        $config['total_rows'] = $allcount;
        $config['per_page'] = $rowperpage;

        $config['full_tag_open'] = "<ul class='pagination'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tagl_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        $config['attributes'] = ['class' => 'pagination_application'];
        $this->pagination->initialize($config);

        $data['pagination'] = $this->pagination->create_links();
        $data['applications'] = $applications;
        $data['row'] = $rowno;

        $data["html"] = $this->load->view('email/show_application', $data, true);

        echo json_encode($data);
    }

    /*
     *
     * Composing Email
     *
     */

    public function ajax_load_instant_tool_email_template()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session || !isset($this->allowed_roles[$this->session->utilihub_hub_target_role])) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        $partner_id = $this->input->post('partner');
        $application_id = $this->input->post('application');
        $user_id = $this->input->post('user');
        $from = $this->input->post('from');
        $to = $this->input->post('to');

        // geet data to replace
        $app_first_name = $this->input->post('app_first_name');
        $app_partner_hotline = $this->input->post('app_partner_hotline');
        $app_ref_code = $this->input->post('app_ref_code');
        $app_user_name = $this->input->post('app_user_name');
        $app_portal_name = $this->input->post('app_portal_name');
        $app_move_in_date = $this->input->post('app_move_in_date');
        $app_email_template = $this->input->post('app_email_template');
        $app_full_name = $this->input->post('app_full_name');
        $app_new_address = $this->input->post('app_new_address');
        $app_partner_name = $this->input->post('app_partner_name');

        // get template to replace
        $template = $this->communications_model->get_email_template($app_email_template, $partner_id);

        if (!$template) {
            echo json_encode(['successful' => false, 'error' => ERROR_400]);
            return;
        }
        // get parnter reference code
        $partner_fields = ['reference_code', 'id'];
        $dataset_partner = $this->partner_model->get_partner_info($partner_id, $partner_fields);
        $partner_reference = $dataset_partner['reference_code'];

        $long_url = $this->create_share_url(0, $app_ref_code, $partner_reference);
        $tiny_code = $this->short_url_model->set_url($long_url);
        $appendChar = (strpos($long_url, '?') === false) ? '?' : '&';

        if ($tiny_code === false || !$this->short_url_model->update_url($tiny_code, $long_url . $appendChar . 'id=' . $tiny_code . '&target=5') || !$this->application_model->set_application_tiny_url_code($application_id, $tiny_code)) {
            echo json_encode(['successful' => false, 'error' => ERROR_400]);
            return;
        }

        $application_data = $this->application_model->get_application_customer_info($application_id);
        if (!$application_data || count($application_data) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_400]);
            return;
        }

        //PARTNER AGENT DATA
        $partner_agent_data = $this->partner_model->get_partner_agent_info($application_data['agent_referred']);
        if (count($partner_agent_data) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_400]);
            return;
        }

        //replace tags in templates
        $search_for = [
            "[APPFIRSTNAME]",
            "[PARTNERHOTLINE]",
            "[REFERENCECODE]",
            "[USERFIRSTNAME]",
            "[SYSTEMNAME]",
            "[PARTNERNAME]",
            "[MOVEINDATE]",
            "[USERFULLNAME]",
            "[NEWADDRESS]",
            "[BUSINESSNAME]",
            "[TINYURL]",
            "[AGENTREFERRED]",
        ];
        $replace_with = [
            $app_first_name,
            $app_partner_hotline,
            $app_ref_code,
            $app_user_name,
            $app_portal_name,
            $app_portal_name,
            $app_move_in_date,
            $app_full_name,
            $app_new_address,
            $app_partner_name,
            ($this->config->item('mhub_tiny_url') . $tiny_code),
            $partner_agent_data['first_name']
        ];

        $email_signature = $this->email_library->get_partner_agent_email_signature($partner_id, $this->session->utilihub_hub_user_id);

        $html_template = str_replace($search_for, $replace_with, $template['html_template'] . $email_signature);

        //[SETCALLBACKLINK]
        //check if schedule callback link is required
        $schedule_callback_needed = strpos($html_template, "[SETCALLBACKLINK]") === false ? false : true;
        if ($schedule_callback_needed) {
            $tiny_code_url = $this->create_scheduled_callback_link($partner_reference, $app_ref_code, $application_id);
            if ($tiny_code_url == false) {
                echo json_encode(['successful' => false, 'error' => ERROR_401]);
            }
            $html_template = str_replace('[SETCALLBACKLINK]', $tiny_code_url, $html_template);
        }

        //[CUSTOMERPORTALTINYURL]
        // Note our use of ===.  Simply == would not work as expected because the position may be 0th (first) character.
        $share_link_needed = strpos($html_template, "[CUSTOMERPORTALTINYURL]") === false ? false : true;
        if ($share_link_needed) {
            $tmp_tiny_url = get_short_url_link_customer_portal($partner_reference, $app_ref_code, $application_id);
            $html_template = str_replace('[CUSTOMERPORTALTINYURL]', $tmp_tiny_url, $html_template);
        }

        //[CUSTOMERPORTALV2TINYURL]
        // Note our use of ===.  Simply == would not work as expected because the position may be 0th (first) character.
        $share_link_needed = strpos($html_template, "[CUSTOMERPORTALV2TINYURL]") === false ? false : true;
        if ($share_link_needed) {
            $tmp_tiny_url = get_short_url_link_customer_portal_v2_application($dataset_partner['id'], $app_ref_code, $application_id);
            $html_template = str_replace('[CUSTOMERPORTALV2TINYURL]', $tmp_tiny_url, $html_template);
        }


        echo json_encode(['itoolEmailSubject' => $template['subject'], 'itoolEmailBody' => $html_template]);
    }

    protected function create_share_url($url_id, $application_ref, $partner_code)
    {
        $tmp_apps_url = !empty($this->config->item('mhub_apps_alternative_url')) ? $this->config->item('mhub_apps_alternative_url') : $this->config->item('mhub_apps_url');
        $search_values = ['[APPSURL]', '[PARTNERCODE]', '[PARTNERCODE_ENC]', '[REFERENCECODE_ENC]'];
        $replacement_values = [$tmp_apps_url, $partner_code, $this->encryption->url_encrypt($partner_code), $this->encryption->url_encrypt($application_ref)];
        $return_url = $this->config->item('mm8_share_url')['url'];
        $partner_id = $this->partner_model->map_partner($partner_code);
        if (intval($url_id) > 0) {
            //fetch details
            $url_info = $this->partner_model->get_share_url_details($partner_id, $url_id);
            $return_url = $url_info['url'];
        }
        $return_url = str_replace($search_values, $replacement_values, $return_url);
        //replace microsite id if present
        if (strpos($return_url, '[MICROSITEID]') !== false) {
            $microsite_data = $this->partner_microsite_model->get_microsite($partner_id);
            $subdomain = isset($microsite_data['domain_name']) ? $microsite_data['domain_name'] : '';
            $return_url = str_replace('[MICROSITEID]', $subdomain, $return_url);
        }

        return $return_url;
    }

    protected function create_scheduled_callback_link($partner_reference, $app_ref_code, $application_id)
    {
        $tmp_apps_url = !empty($this->config->item('mhub_apps_alternative_url')) ? $this->config->item('mhub_apps_alternative_url') : $this->config->item('mhub_apps_url');
        $long_url = $tmp_apps_url . 'scheduled-callback/start/' . $partner_reference . '/' . $this->encryption->url_encrypt($app_ref_code) . '/';
        $tiny_code = $this->short_url_model->set_url($long_url);
        if ($tiny_code === false || !$this->application_model->set_application_tiny_url_code($application_id, $tiny_code)) {
            return false;
        }
        return $tiny_code_url = $this->config->item('mhub_tiny_url') . $tiny_code;
    }

    public function email_html($log_email_id = 0)
    {
        if (!$this->validate_access()) {
            return;
        }

        if (empty($log_email_id)) {
            $this->load->view('errors/restricted_page');
        }

        $currentLogEmail = $this->log_email_model->getById($log_email_id);
        if (!$currentLogEmail) {
            $this->load->view('errors/restricted_page');
            return;
        }

        if (empty($currentLogEmail->partner_id) && empty($currentLogEmail->application_id)) {
            $this->load->view('errors/restricted_page');
            return;
        }

        if ($currentLogEmail->application_id) {
            $application = $this->application_model->get_application_customer_info($currentLogEmail->application_id);
            if (count($application) <= 0 || $application['partner_id'] != $this->session->utilihub_hub_active_partner_id) {
                $this->load->view('errors/restricted_page');
                return;
            }
        }

        $currentLogEmail->html_message = redact_href_tags($currentLogEmail->html_message);
        $currentLogEmail->html_message = redact_short_url_link($currentLogEmail->html_message);
        echo $currentLogEmail->html_message;

        exit();
    }

    private function _getLastEmailScrap()
    {
        $dateTimeLastEmailScrap = null;

        $google_authenticate = $this->backend_google_authenticate_model->getByName('hub_gmail_scrapper');
        if ($google_authenticate) {
            $date = new DateTime($google_authenticate->date_cron_gmail_scrap_last_run);
            $dateTimeLastEmailScrap = $date->format($this->config->item('mm8_php_default_date_format') . " h:i:s A");
        }

        return $dateTimeLastEmailScrap;
    }
}
