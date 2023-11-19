<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Profile extends CI_Controller
{
    protected $main_navigator_title = 'Hub';
    //used by roles: USER_ADMIN
    protected $partner_data = null;
    protected $agent_data = null;
    protected $accessible_modules = null;

    public function __construct()
    {
        parent::__construct();

        // sentry metadata
        if (SENTRY_ENABLED) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
                $scope->setUser([
                    'id' => $this->session->utilihub_hub_user_profile_email,
                    'username' => $this->session->utilihub_hub_user_code,
                    'email' => $this->session->utilihub_hub_user_profile_email,
                    'role_id' => $this->session->utilihub_hub_user_role,
                    'full_name' => $this->session->utilihub_hub_user_profile_fullname,
                    'target_id' => $this->session->utilihub_hub_target_id,
                    'target_role' => $this->session->utilihub_hub_target_role,
                    'active_partner_id' => $this->session->utilihub_hub_active_partner_id,
                    'active_agent_id' => $this->session->utilihub_hub_active_agent_id
                ]);
            });

            \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
                $scope->setTag('country_code', $this->config->item('mm8_country_code'));
                $role_tag = $this->config->item('mm8_agent_role_levels')[$this->session->utilihub_hub_target_role];
                if (!is_null($role_tag) && !empty($role_tag)) {
                    $scope->setTag('role', $role_tag);
                }
                $scope->setTag('session_id', $this->session->session_id);
            });
        }
        // end sentry meta

        $this->load->model('agent_model');
        $this->load->model('cs_agent_model');
        $this->load->model('partner_model');
        $this->load->model('subscription_model');
        $this->load->model('agent_microsite_model');

        $this->load->model('account_manager_model');
        $this->load->model('account_manager_log_email_model');
        $this->load->model('manager_model');
        $this->load->model('communications_model');

        $this->load->library('form_validation');
        $this->load->library('account_manager_library');

        //form lookup
        $library_name = '/Form_lookup_library_' . $this->config->item('mm8_country_code');
        if (!file_exists(APPPATH . "libraries/" . $library_name . ".php")) {
            $library_name = '/Form_lookup_library_AU';
        }
        $this->load->library($library_name, '', 'form_lookup_library');

        //address lookup
        $library_name = '/Kleber_datatools_' . $this->config->item('mm8_country_code');
        if (!file_exists(APPPATH . "libraries/" . $library_name . ".php")) {
            $library_name = '/Kleber_datatools_AU';
        }
        $host = ENVIRONMENT == "production" ? $this->config->item('mm8_kleber_prod_url') : $this->config->item('mm8_kleber_dev_url');
        $this->load->library($library_name, ['host' => $host], 'kleber_datatools');

        //s3
        $this->load->library('aws_s3_library', ['bucket_name' => $this->config->item('mm8_aws_default_bucket')], 'aws_s3_library_public');

        //ses
        $this->load->library('aws_ses_library');

        //stripe
        $this->load->library('stripe_library');
        $this->load->model('stripe_accounts_model');

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

        $this->load->library('connect_sd_library');
        $this->connect_sd_library->getSessionChatChannel(CONNECT_SD_APP_HUB); // Connect SD is on all pages, check active/inactive chat channel
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        switch ($this->session->utilihub_hub_user_role) {
            case USER_ADMIN:
                $this->partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
                if (count($this->partner_data) <= 0) {
                    //planning to redirect to landing page but may cause an infinite loop!
                    redirect('login', 'refresh');
                }
                break;
            default:
                break;
        }

        $this->account_manager_library->access_log();
    }

    public function index()
    {
        $this->validate_access();

        //this ensures the user is reset to its main workspace
        //before loading page
        $dt_params = $this->input->get();
        if (isset($dt_params['home'])) {
            $url = base_url() . 'login/touch-base/?caller=' . $this->encryption->url_encrypt(current_url());
            redirect($url, 'refresh');
        }


        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        if ($user_profile === false || count($user_profile) <= 0) {
            $this->load->view('errors/restricted_page');
            return;
        }

        if ((int) $user_profile['verified'] === STATUS_NG) {
            redirect('login', 'refresh');
        }


        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "main_profile";
        $view_data['main_navigator_title'] = $this->main_navigator_title;

        if (!empty($this->partner_data)) {
            $view_data['package_type'] = $this->partner_data['package_type'];
            $view_data['partner_active'] = $this->partner_data['active'];
            $view_data['partner_data'] = $this->partner_data;
        }

        if (!empty($this->agent_data)) {
            $view_data['agent_active'] = $this->agent_data['active'];
            $view_data['agent_data'] = $this->agent_data;
        }

        if (!empty($this->accessible_modules)) {
            $view_data['accessible_modules'] = $this->accessible_modules;
        }


        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/jasny/jasny-bootstrap.min.css',
            asset_url() . 'css/plugins/iCheck/line/line.css',
            asset_url() . 'css/plugins/iCheck/square/blue.css',
            asset_url() . 'css/plugins/dataTables/dataTables.bootstrap.css',
            asset_url() . 'css/plugins/dataTables/dataTables.responsive.css',
            asset_url() . 'css/plugins/dataTables/dataTables.tableTools.min.css'
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/jasny/jasny-bootstrap.min.js',
            asset_url() . 'js/plugins/bootstrap3-typeahead/bootstrap3-typeahead.js',
            asset_url() . 'js/plugins/clipboard/clipboard.min.js',
            asset_url() . 'js/plugins/iCheck/icheck.min.js',
            asset_url() . 'js/plugins/dataTables/jquery.dataTables.js',
            asset_url() . 'js/plugins/dataTables/dataTables.bootstrap.js',
            asset_url() . 'js/plugins/dataTables/dataTables.responsive.js',
            asset_url() . 'js/plugins/dataTables/dataTables.tableTools.min.js',
            asset_url() . 'js/hub-profile.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load()";
        $view_data["user_profile"] = $user_profile;

        // Overview defaults
        $view_data['saved_filter'] = [];

        if (!empty($this->agent_data['overview_defaults'])) {
            $overviewDefaults = json_decode($this->agent_data['overview_defaults'], true);
            if (json_last_error() == JSON_ERROR_NONE) {
                $view_data['saved_filter']['wallet'] = isset($overviewDefaults['profile_wallet_overview']) ? $overviewDefaults['profile_wallet_overview'] : '';
                $view_data['saved_filter']['rcti'] = isset($overviewDefaults['profile_rcti_overview']) ? $overviewDefaults['profile_rcti_overview'] : '';
            }
        }

        if (count($this->input->post()) <= 0) {

            //kb explainer?
            $kb_code = 'main_profile';
            if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
                $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
                $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
            } else {
                $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
                $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
            }


            $additional_data = [];

            if ($user_profile['role'] == USER_ADMIN) {
                $additional_data['partner_data'] = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
                if (count($additional_data['partner_data']) <= 0) {
                    $this->load->view('errors/restricted_page');
                    return;
                }

                //list of email accounts
                $tmp_lookup_email_accounts = $this->agent_model->get_agent_email_address_identities_list($this->session->utilihub_hub_user_id, true);
                if (!empty($additional_data['partner_data']['default_ops_email'])) {
                    array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['default_ops_email']);
                }
                if (!empty($additional_data['partner_data']['ops_email'])) {
                    array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['ops_email']);
                }
                if (!empty($additional_data['partner_data']['ops_email_reply_to'])) {
                    array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['ops_email_reply_to']);
                }
                if (!empty($user_profile['default_from_email'])) {
                    array_unshift($tmp_lookup_email_accounts, $user_profile['default_from_email']);
                }
                if (!empty($user_profile['default_reply_to_email'])) {
                    array_unshift($tmp_lookup_email_accounts, $user_profile['default_reply_to_email']);
                }

                $additional_data['lookup_email_accounts'] = array_unique($tmp_lookup_email_accounts);
            }

            //unsubscribe
            $additional_data['email_group_data'] = $this->subscription_model->get_email_groups_for_partner_agent_role($user_profile['role']);
            $additional_data['subscription_data'] = $this->subscription_model->get_subscription_for_partner_agent($this->session->utilihub_hub_user_id);

            $additional_data['subscription_categories'] = $this->subscription_model->get_subscription_categories();
            $additional_data['subscription_categories_settings'] = $this->subscription_model->get_subscription_categories_by_email($this->session->utilihub_hub_user_profile_email);

            $this->load->view('template_header', $view_data);
            $this->load->view('template_mainmenu', $view_data);
            $this->load->view('template_submenu', $view_data);
            $this->load->view('profile/main_profile', array_merge($view_data, $additional_data));
            $this->load->view('template_footer', $view_data);
        } else {

            //ACTION
            $dataset = $this->input->post();

            //process file uploads
            $profile_photo = self::process_input_file('profile_photo');
            if (!$profile_photo['successful']) {
                $this->session->set_flashdata('action_message_failed', $profile_photo['error']);

                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }


            $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
            $this->form_validation->set_message('matches', 'Passwords do not match');

            //1. profile
            $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
            $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email|callback_change_user_email_available[' . $user_profile['email'] . ']');

            /**
             * CODE BRANCHING HERE - COUNTRY
             *      AU
             *      NZ
             *      US
             *      UK
             */
            switch ($this->config->item('mm8_country_code')) {
                case "AU":
                case "US": {
                        if ($dataset['preferred_phone_number'] == 'mobile') {
                            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|exact_length[10]');
                        } else {
                            $this->form_validation->set_rules('office_phone', 'Office Phone', 'trim|required|numeric|exact_length[10]');
                            $this->form_validation->set_rules('office_extension', 'Office Extension', 'trim|numeric');
                        }
                        break;
                    }
                case "NZ": {
                        if ($dataset['preferred_phone_number'] == 'mobile') {
                            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|min_length[9]|max_length[11]');
                        } else {
                            $this->form_validation->set_rules('office_phone', 'Office Phone', 'trim|required|numeric|min_length[9]|max_length[11]');
                            $this->form_validation->set_rules('office_extension', 'Office Extension', 'trim|numeric');
                        }
                        break;
                    }
                case "UK": {
                        if ($dataset['preferred_phone_number'] == 'mobile') {
                            $this->form_validation->set_rules('mobile_phone', 'Mobile Phone', 'trim|required|numeric|exact_length[11]');
                        } else {
                            $this->form_validation->set_rules('office_phone', 'Office Phone', 'trim|required|numeric|exact_length[11]');
                            $this->form_validation->set_rules('office_extension', 'Office Extension', 'trim|numeric');
                        }
                        break;
                    }
                default:
                    break;
            }


            //2. payment details
            $this->form_validation->set_rules('payment_method', 'Payment Method', 'required');
            /**
             * CODE BRANCHING HERE - COUNTRY
             *      AU
             *      NZ
             *      US
             *      UK
             */
            switch ($this->config->item('mm8_country_code')) {
                case "AU":
                    switch ((int) $dataset['payment_method']) {
                        case PAY_BY_DEBIT_MASTERCARD:
                            $this->form_validation->set_rules('user_abn', 'ABN', 'trim|exact_length[11]');
                            $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                            break;
                        case PAY_BY_BANK_TRANSFER:
                            $this->form_validation->set_rules('user_abn', 'ABN', 'trim|required|exact_length[11]');
                            $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                            $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                            $this->form_validation->set_rules('bank_bsb', 'BSB', 'trim|required|numeric|exact_length[6]');
                            break;
                        case PAY_BY_PAYPAL:
                            $this->form_validation->set_rules('user_abn', 'ABN', 'trim|required|exact_length[11]');
                            $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                            break;
                        default:
                            $this->form_validation->set_rules('user_abn', 'ABN', 'trim|exact_length[11]');
                            break;
                    }
                    break;
                case "NZ":
                    switch ((int) $dataset['payment_method']) {
                        case PAY_BY_DEBIT_MASTERCARD:
                            $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                            break;
                        case PAY_BY_BANK_TRANSFER:
                            $this->form_validation->set_rules('user_irdn', 'IRDN', 'trim|min_length[8]|max_length[9]');
                            $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                            $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                            break;
                        case PAY_BY_PAYPAL:
                            $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                            break;
                        default:
                            break;
                    }
                    break;
                case "US":
                    switch ((int) $dataset['payment_method']) {
                        case PAY_BY_DEBIT_MASTERCARD:
                            $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                            break;
                        case PAY_BY_BANK_TRANSFER:
                            $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                            $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                            $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
                            $this->form_validation->set_rules('bank_routing_number', 'Routing Number', 'trim|required|numeric');
                            break;
                        case PAY_BY_PAYPAL:
                            $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                            break;
                        default:
                            break;
                    }
                    break;
                case "UK":
                    switch ((int) $dataset['payment_method']) {
                        case PAY_BY_DEBIT_MASTERCARD:
                            $this->form_validation->set_rules('prepaid_mastercard_debit_address', 'Postal Address', 'trim|required');
                            break;
                        case PAY_BY_BANK_TRANSFER:
                            $this->form_validation->set_rules('user_crn', 'CRN', 'trim|exact_length[8]');
                            $this->form_validation->set_rules('bank_acc_name', 'Account Name', 'trim|required');
                            $this->form_validation->set_rules('bank_acc_no', 'Account Number', 'trim|required|numeric');
                            $this->form_validation->set_rules('bank_sort_code', 'Sort Code', 'trim|required|numeric|exact_length[6]');
                            break;
                        case PAY_BY_PAYPAL:
                            $this->form_validation->set_rules('paypal_account', 'Email Address', 'trim|required|valid_email');
                            break;
                        default:
                            break;
                    }
                    break;
                default:
                    break;
            }
            //set rules for payment_summary_email_cc
            $this->form_validation->set_rules('payment_summary_email_cc', 'Email Address', 'trim|valid_email');

            //3. password
            if (!empty($dataset['old_password']) && !empty($dataset['new_password']) && !empty($dataset['new_password_confirm'])) {
                $this->form_validation->set_rules('old_password', 'Password', 'trim|required|callback_check_old_password[' . $user_profile['password'] . ']');
                $this->form_validation->set_rules('new_password', 'Password', 'trim|required|max_length[128]|callback_check_password_complexity');
                $this->form_validation->set_rules('new_password_confirm', 'Password', 'trim|required|matches[new_password]');
            }


            if ($this->form_validation->run() == false) {
                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }


            //START
            $this->db->trans_begin();

            //SAVE DATA
            $user_data = [];
            $user_data['first_name'] = trim($dataset['first_name']);
            $user_data['last_name'] = trim($dataset['last_name']);
            $user_data['full_name'] = $user_data['first_name'] . " " . $user_data['last_name'];
            $user_data['email'] = $dataset['user_email'];
            $user_data['mobile_phone'] = $dataset['mobile_phone'];
            $user_data['office_phone'] = $dataset['office_phone'];
            $user_data['office_extension'] = $dataset['office_extension'];
            $user_data['preferred_phone_number'] = $dataset['preferred_phone_number'];
            $user_data['position'] = $dataset['position'];
            $user_data['about'] = trim($dataset['agent_about']);

            if (isset($profile_photo['file']) && !empty($profile_photo['file'])) {
                $user_data['profile_photo'] = $profile_photo['file'];
            }


            //email sender
            if (isset($dataset['default_from_email']) && !empty($dataset['default_from_email']) && isset($dataset['default_reply_to_email']) && !empty($dataset['default_reply_to_email'])) {
                $user_data['default_from_email'] = $dataset['default_from_email'];
                $user_data['default_reply_to_email'] = $dataset['default_reply_to_email'];
            }

            //password
            if (!empty($dataset['old_password']) && !empty($dataset['new_password']) && !empty($dataset['new_password_confirm'])) {
                $user_data['password'] = better_crypt($dataset['new_password']);
                $user_data['last_password_reset'] = $this->database_tz_model->now();
                $user_data['verification_code'] = null;
                $user_data['google_user_id'] = null;
                $user_data['lock_expiry'] = null;
                $user_data['failed_attempts'] = "0";

                $this->dashboard_user_model->log_audit_trail(null, "password_reset", json_encode(["id" => $this->session->utilihub_hub_user_id, "email" => $user_data['email']]));
            }

            //payment method
            $user_data['abn'] = isset($dataset['user_abn']) && $dataset['user_abn'] != "" ? $dataset['user_abn'] : null;
            $user_data['irdn'] = isset($dataset['user_irdn']) && $dataset['user_irdn'] != "" ? $dataset['user_irdn'] : null;
            $user_data['crn'] = isset($dataset['user_crn']) && $dataset['user_crn'] != "" ? $dataset['user_crn'] : null;

            if (isset($dataset['payment_method'])) {
                switch ((int) $dataset['payment_method']) {
                    case PAY_BY_DEBIT_MASTERCARD:
                        $user_data['payment_method'] = $dataset['payment_method'];
                        $user_data['prepaid_mastercard_debit_address'] = $dataset['prepaid_mastercard_debit_address'];
                        break;
                    case PAY_BY_BANK_TRANSFER:
                        /**
                         * CODE BRANCHING HERE - COUNTRY
                         *      AU
                         *      NZ
                         *      US
                         *      UK
                         */
                        switch ($this->config->item('mm8_country_code')) {
                            case "AU":
                                $user_data['payment_method'] = $dataset['payment_method'];
                                $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                                $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                                $user_data['bank_bsb'] = $this->encryption->encrypt($dataset['bank_bsb']);
                                break;
                            case "NZ":
                                $user_data['payment_method'] = $dataset['payment_method'];
                                $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                                $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                            // no break
                            case "US":
                                $user_data['payment_method'] = $dataset['payment_method'];
                                $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                                $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                                $user_data['bank_name'] = $this->encryption->encrypt($dataset['bank_name']);
                                $user_data['bank_routing_number'] = $this->encryption->encrypt($dataset['bank_routing_number']);
                                break;
                            case "UK":
                                $user_data['payment_method'] = $dataset['payment_method'];
                                $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                                $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                                $user_data['bank_sort_code'] = $this->encryption->encrypt($dataset['bank_sort_code']);
                                break;
                            default:
                                break;
                        }
                        break;
                    case PAY_BY_PAYPAL:
                        $user_data['payment_method'] = $dataset['payment_method'];
                        $user_data['paypal_account'] = $dataset['paypal_account'];
                        break;
                    case PAY_BY_SKIP:
                        $user_data['payment_method'] = $dataset['payment_method'];
                        break;
                    case PAY_BY_DEBIT_VISA:
                        $user_data['payment_method'] = $dataset['payment_method'];
                        $user_data['prepaid_visa_debit_address'] = $dataset['prepaid_visa_debit_address'];
                        break;
                    default:
                        break;
                }
            }

            //payment_summary_email_cc
            if (isset($dataset['payment_summary_email_cc'])) {
                $user_data['payment_summary_email_cc'] = $dataset['payment_summary_email_cc'];
            }

            //email settings
            if (isset($dataset['email_signature'])) {
                $user_data['email_signature'] = trim($dataset['email_signature']) != "" ? trim($dataset['email_signature']) : null;
            }


            //save
            if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('action_message_failed', "Profile update failed! (ERROR_502)");

                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }


            //update children profiles (if there are any)
            if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('action_message_failed', "Profile update failed! (ERROR_502)");

                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }

            //unsubscribe
            $i = 0;
            $new_unsubscribe_data = [];

            $email_group_data = $this->subscription_model->get_email_groups_for_partner_agent_role($user_profile['role']);

            if ($dataset != "") {
                $group_exclude = [];
                foreach ($dataset as $k => $v) {
                    $str = preg_replace('/^email_group_/', '', $k);
                    if (strpos($k, "email_group") !== false) {
                        $group_exclude[$i] = $str;
                        $str = preg_replace('/^email_group_/', '', $k);
                        $i++;
                    }
                }
                $j = 0;
                foreach ($email_group_data as $key => $email_group_each) {
                    if (empty($group_exclude)) {
                        $new_unsubscribe_data[$j]['group_id'] = $email_group_each['id'];
                        $new_unsubscribe_data[$j]['agent_id'] = $this->session->utilihub_hub_user_id;
                        $new_unsubscribe_data[$j]['unsubscribe'] = STATUS_OK;
                        $j++;
                    } elseif (!in_array($email_group_each['id'], $group_exclude)) {
                        $new_unsubscribe_data[$j]['group_id'] = $email_group_each['id'];
                        $new_unsubscribe_data[$j]['agent_id'] = $this->session->utilihub_hub_user_id;
                        $new_unsubscribe_data[$j]['unsubscribe'] = STATUS_OK;
                        $j++;
                    }
                }
            }


            if ($this->subscription_model->update_subscriptions_partner_agent($new_unsubscribe_data, $this->session->utilihub_hub_user_id) === false) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('action_message_failed', "Profile update failed! (ERROR_502)");

                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();

                $this->session->set_flashdata('action_message_failed', "Profile update failed! (ERROR_502)");

                $this->load->view('template_header', $view_data);
                $this->load->view('template_mainmenu', $view_data);
                $this->load->view('template_submenu', $view_data);
                $this->load->view('profile/main_profile', $view_data);
                $this->load->view('template_footer', $view_data);
                return;
            }

            $this->db->trans_commit();

            //update session
            $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
            if ($user_profile === false || count($user_profile) <= 0) {
                redirect('login', 'refresh');
            }

            $this->session->utilihub_hub_user_profile_first_name = $user_profile['first_name'];
            $this->session->utilihub_hub_user_profile_last_name = $user_profilet['last_name'];
            $this->session->utilihub_hub_user_profile_fullname = $user_profile['full_name'];
            $this->session->utilihub_hub_user_profile_initials = strtoupper(substr($user_profile['first_name'], 0, 1) . substr($user_profile['last_name'], 0, 1));
            $this->session->utilihub_hub_user_profile_email = $user_profile['email'];
            $this->session->utilihub_hub_user_profile_photo = isset($user_profile['profile_photo']) && !empty($user_profile['profile_photo']) ? $user_profile['profile_photo'] : asset_url() . "img/default/profile-photo.jpg";

            $this->session->set_flashdata('action_message_success', "Profile updated!");
            redirect(current_url());
        }
    }

    public function ajax_profile_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        $profile_photo = self::process_input_file('profile_photo');
        if (!$profile_photo['successful']) {
            echo json_encode(['successful' => false, 'error' => $profile_photo['error']]);
            return;
        }

        if (!$this->change_user_email_available($dataset['user_email'], $user_profile['email'])) {
            echo json_encode(['successful' => false, 'error' => ERROR_600]);
            return;
        }


        if (isset($dataset['microsite_id']) && !empty($dataset['microsite_id'])) {
            if ($this->agent_microsite_model->microsite_id_exists($dataset['microsite_id'], $this->session->utilihub_hub_user_id)) {
                echo json_encode(['successful' => false, 'error' => 'Microsite ID already in-use']);
                return;
            }
        }

        //SAVE DATA
        $user_data = [];
        $user_data['first_name'] = trim($dataset['first_name']);
        $user_data['last_name'] = trim($dataset['last_name']);
        $user_data['full_name'] = $user_data['first_name'] . " " . $user_data['last_name'];
        $user_data['email'] = $dataset['user_email'];
        $user_data['mobile_phone'] = $dataset['mobile_phone'];
        $user_data['position'] = $dataset['position'];
        $user_data['about'] = trim($dataset['agent_about']);
        $user_data['preferred_phone_number'] = $dataset['preferred_phone_number'];
        $user_data['office_phone'] = $dataset['office_phone'];
        $user_data['office_extension'] = $dataset['office_extension'];

        if (isset($dataset['microsite_id']) && !empty($dataset['microsite_id'])) {
            $user_data['microsite_id'] = $dataset['microsite_id'];
        }


        if (isset($profile_photo['file']) && !empty($profile_photo['file'])) {
            $user_data['profile_photo'] = $profile_photo['file'];
        }

        if (isset($dataset['microsite_id']) && !empty($dataset['microsite_id'])) {
            $user_data['microsite_id'] = $dataset['microsite_id'];
        }

        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        //STRIPE
        $stripe_account = $this->stripe_accounts_model->getByAgentId($this->session->utilihub_hub_user_id);
        if (!empty($stripe_account) && isset($stripe_account->customer) && !empty($stripe_account->customer)) {
            //update details
            $tmp_results = $this->stripe_library->update_customer($stripe_account->customer, '', $user_data['full_name'], $user_data['email']);
            if (!$tmp_results['successful']) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_606)"]);
                return;
            }
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();

        //update session
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->session->utilihub_hub_user_profile_fullname = $user_profile['full_name'];
        $this->session->utilihub_hub_user_profile_initials = strtoupper(substr($user_profile['first_name'], 0, 1) . substr($user_profile['last_name'], 0, 1));
        $this->session->utilihub_hub_user_profile_email = $user_profile['email'];
        $this->session->utilihub_hub_user_profile_photo = isset($user_profile['profile_photo']) && !empty($user_profile['profile_photo']) ? $user_profile['profile_photo'] : asset_url() . "img/default/profile-photo.jpg";
        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        echo json_encode(['successful' => true]);
    }

    public function ajax_profile_security_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //password
        if (!empty($dataset['old_password']) && !empty($dataset['new_password']) && !empty($dataset['new_password_confirm'])) {
            //get user profile
            $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

            if (!$this->check_old_password($dataset['old_password'], $user_profile['password'])) {
                echo json_encode(['successful' => false, 'error' => 'Invalid password']);
                return;
            }
            $user_data = [];
            $user_data['password'] = better_crypt($dataset['new_password']);
            $user_data['last_password_reset'] = $this->database_tz_model->now();
            $user_data['verification_code'] = null;
            $user_data['google_user_id'] = null;
            $user_data['lock_expiry'] = null;
            $user_data['failed_attempts'] = "0";

            $this->dashboard_user_model->log_audit_trail(null, "password_reset", json_encode(["id" => $this->session->utilihub_hub_user_id, "email" => $user_profile['email']]));
            //START
            $this->db->trans_begin();

            //save
            if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
                return;
            }


            //update children profiles (if there are any)
            if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

            echo json_encode(['successful' => true]);
            return;
        }

        echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
        return;
    }

    public function ajax_profile_email_settings_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        $user_data = [];
        //email sender
        if (isset($dataset['default_from_email']) && !empty($dataset['default_from_email']) && isset($dataset['default_reply_to_email']) && !empty($dataset['default_reply_to_email'])) {
            $user_data['default_from_email'] = $dataset['default_from_email'];
            $user_data['default_reply_to_email'] = $dataset['default_reply_to_email'];
        }

        //email settings
        if (isset($dataset['email_signature'])) {
            $user_data['email_signature'] = trim($dataset['email_signature']) != "" ? trim($dataset['email_signature']) : null;
        }

        if (count($user_data) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);

            return;
        }

        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
        return;
    }

    public function ajax_profile_rewards_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //SAVE DATA
        $user_data = [];

        $user_data['abn'] = isset($dataset['user_abn']) && $dataset['user_abn'] != "" ? $dataset['user_abn'] : null;
        $user_data['irdn'] = isset($dataset['user_irdn']) && $dataset['user_irdn'] != "" ? $dataset['user_irdn'] : null;
        $user_data['crn'] = isset($dataset['user_crn']) && $dataset['user_crn'] != "" ? $dataset['user_crn'] : null;

        //auto payout
        if (isset($dataset['auto_payout_enabled']) && (int) $dataset['auto_payout_enabled'] == STATUS_OK) {
            $user_data['auto_payout_enabled'] = STATUS_OK;
        } else {
            $user_data['auto_payout_enabled'] = STATUS_NG;
        }


        if (isset($dataset['payment_method'])) {
            switch ((int) $dataset['payment_method']) {
                case PAY_BY_DEBIT_MASTERCARD:
                    $user_data['payment_method'] = $dataset['payment_method'];
                    $user_data['prepaid_mastercard_debit_address'] = $dataset['prepaid_mastercard_debit_address'];
                    break;
                case PAY_BY_BANK_TRANSFER:
                    /**
                     * CODE BRANCHING HERE - COUNTRY
                     *      AU
                     *      NZ
                     *      US
                     *      UK
                     */
                    switch ($this->config->item('mm8_country_code')) {
                        case "AU":
                            $user_data['payment_method'] = $dataset['payment_method'];
                            $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                            $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                            $user_data['bank_bsb'] = $this->encryption->encrypt($dataset['bank_bsb']);
                            break;
                        case "NZ":
                            $user_data['payment_method'] = $dataset['payment_method'];
                            $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                            $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                            break;
                        case "US":
                            $user_data['payment_method'] = $dataset['payment_method'];
                            $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                            $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                            $user_data['bank_name'] = $this->encryption->encrypt($dataset['bank_name']);
                            $user_data['bank_routing_number'] = $this->encryption->encrypt($dataset['bank_routing_number']);
                            break;
                        case "UK":
                            $user_data['payment_method'] = $dataset['payment_method'];
                            $user_data['bank_acc_name'] = $this->encryption->encrypt($dataset['bank_acc_name']);
                            $user_data['bank_acc_no'] = $this->encryption->encrypt($dataset['bank_acc_no']);
                            $user_data['bank_sort_code'] = $this->encryption->encrypt($dataset['bank_sort_code']);
                            break;
                        default:
                            break;
                    }
                    break;
                case PAY_BY_PAYPAL:
                    $user_data['payment_method'] = $dataset['payment_method'];
                    $user_data['paypal_account'] = $dataset['paypal_account'];
                    break;
                case PAY_BY_SKIP:
                    $user_data['payment_method'] = $dataset['payment_method'];
                    //force auto pay to be disabled
                    $user_data['auto_payout_enabled'] = STATUS_NG;
                    break;
                case PAY_BY_DEBIT_VISA:
                    $user_data['payment_method'] = $dataset['payment_method'];
                    $user_data['prepaid_visa_debit_address'] = $dataset['prepaid_visa_debit_address'];
                    break;
                case PAY_BY_THIRD_PARTY_INVOICE:
                    $user_data['payment_method'] = $dataset['payment_method'];
                    $user_data['third_party_invoice_acc_name'] = $this->encryption->encrypt($dataset['third_party_invoice_acc_name']);
                    $user_data['third_party_invoice_acc_no'] = $this->encryption->encrypt($dataset['third_party_invoice_acc_no']);
                    break;
                default:
                    break;
            }

            //payment_summary_email_cc
            if (isset($dataset['payment_summary_email_cc'])) {
                $user_data['payment_summary_email_cc'] = $dataset['payment_summary_email_cc'];
            }


            if (count($user_data) <= 0) {
                echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
                return;
            }

            //START
            $this->db->trans_begin();

            //save
            if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
                return;
            }


            //update children profiles (if there are any)
            if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

            echo json_encode(['successful' => true, 'kk' => $this->session->utilihub_hub_user_id]);
            return;
        }

        echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
        return;
    }

    public function ajax_profile_unsubscribe_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        //unsubscribe
        $i = 0;
        $new_unsubscribe_data = [];

        $email_group_data = $this->subscription_model->get_email_groups_for_partner_agent_role($user_profile['role']);

        //START
        $this->db->trans_begin();

        // subscription categories
        $this->subscription_model->update_subscription_categories($this->session->utilihub_hub_user_profile_email, $dataset['subscription_categories']);

        if ($dataset != "") {
            $group_exclude = [];
            foreach ($dataset as $k => $v) {
                $str = preg_replace('/^email_group_/', '', $k);
                if (strpos($k, "email_group") !== false) {
                    $group_exclude[$i] = $str;
                    $str = preg_replace('/^email_group_/', '', $k);
                    $i++;
                }
            }
            $j = 0;
            foreach ($email_group_data as $key => $email_group_each) {
                if (empty($group_exclude)) {
                    $new_unsubscribe_data[$j]['group_id'] = $email_group_each['id'];
                    $new_unsubscribe_data[$j]['agent_id'] = $this->session->utilihub_hub_user_id;
                    $new_unsubscribe_data[$j]['unsubscribe'] = STATUS_OK;
                    $j++;
                } elseif (!in_array($email_group_each['id'], $group_exclude)) {
                    $new_unsubscribe_data[$j]['group_id'] = $email_group_each['id'];
                    $new_unsubscribe_data[$j]['agent_id'] = $this->session->utilihub_hub_user_id;
                    $new_unsubscribe_data[$j]['unsubscribe'] = STATUS_OK;
                    $j++;
                } elseif (!in_array(2, $dataset['subscription_categories'])) {
                    // if agent is not subscribed to "Reporting" category, then unsubscribe to all of these groups
                    $new_unsubscribe_data[$j]['group_id'] = $email_group_each['id'];
                    $new_unsubscribe_data[$j]['agent_id'] = $this->session->utilihub_hub_user_id;
                    $new_unsubscribe_data[$j]['unsubscribe'] = STATUS_OK;
                    $j++;
                }
            }
        }


        if ($this->subscription_model->update_subscriptions_partner_agent($new_unsubscribe_data, $this->session->utilihub_hub_user_id) === false) {
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

        echo json_encode(['successful' => true]);
        return;
    }

    /**
     *
     * Profile > Email Accounts
     * ONLY FOR USER_ADMIN
     */
    public function email_accounts()
    {
        $this->validate_access();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            $this->load->view('errors/restricted_page');
            return;
        }

        if ((int) $user_profile['verified'] === STATUS_NG) {
            redirect('login', 'refresh');
        }

        if ($user_profile['role'] != USER_ADMIN) {
            //redirect to landing page
            redirect(base_url() . $this->config->item('hub_landing_page')[$user_profile['role']], 'refresh');
        }


        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "main_profile";
        $view_data['main_navigator_title'] = $this->main_navigator_title;

        if (!empty($this->partner_data)) {
            $view_data['package_type'] = $this->partner_data['package_type'];
            $view_data['partner_active'] = $this->partner_data['active'];
        }

        if (!empty($this->agent_data)) {
            $view_data['agent_active'] = $this->agent_data['active'];
        }

        if (!empty($this->accessible_modules)) {
            $view_data['accessible_modules'] = $this->accessible_modules;
        }


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
            asset_url() . 'js/hub-profile-email-accounts.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load()";
        $view_data["user_profile"] = $user_profile;

        $additional_data = [];
        $additional_data['partner_data'] = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
        if (count($additional_data['partner_data']) <= 0) {
            $this->load->view('errors/restricted_page');
            return;
        }


        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('profile/main_profile_email_accounts', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_email_accounts_dt_get_summary()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        $dt_params = $this->input->get();

        $order_col = $dt_params['columns'][$dt_params['order'][0]['column']]['data'];
        $order_dir = $dt_params['order'][0]['dir'];
        $start = $dt_params['start'];
        $length = $dt_params['length'];

        //CONDITIONS
        $conditions_arr = [];
        array_push($conditions_arr, "tbl_partner_agents_email_address.agent_id = " . $this->db->escape($this->session->utilihub_hub_user_id));

        if (isset($dt_params['searchText']) && !empty($dt_params['searchText'])) {
            $search_cols = [
                "email_address" => "tbl_partner_agents_email_address.email_address"
            ];

            $str_search = "";
            for ($i = 0; $i < count($dt_params['columns']); $i++) {
                if (filter_var($dt_params['columns'][$i]['searchable'], FILTER_VALIDATE_BOOLEAN)) {
                    $str_search .= $search_cols[$dt_params['columns'][$i]['data']] . " LIKE " . $this->db->escape('%' . $dt_params['searchText'] . '%') . " OR ";
                }
            }

            if ($str_search != "") {
                $str_search = substr($str_search, 0, strlen($str_search) - 4); //remove last ' OR  '
                array_push($conditions_arr, "(" . $str_search . ")");
            }
        }

        $condition = count($conditions_arr) > 0 ? "WHERE " . implode(" AND ", $conditions_arr) : "";

        $dataset = $this->dashboard_user_model->dt_get_email_address_accounts_summary($order_col, $order_dir, $start, $length, $condition);
        $total_count = $this->dashboard_user_model->dt_get_email_address_accounts_count($condition);

        $dt_data = [];
        $dt_data['draw'] = (int) $dt_params['draw'];
        $dt_data['recordsTotal'] = $total_count;
        $dt_data['recordsFiltered'] = $total_count;
        $dt_data['data'] = [];

        foreach ($dataset as $subset) {
            //whatever you do here update ajax_partners_dt_get_partners_row() as well
            $dt_subset = $subset;

            $dt_subset['DT_RowId'] = "row_" . $subset['id'];
            $dt_subset['DT_RowClass'] = "email-summary-row";
            $dt_subset['DT_RowAttr'] = ['attr-email-address' => $subset['email_address']];

            switch ($subset['verified']) {
                case FAILED:
                    $dt_subset['verified'] = '<span class="badge badge-danger">Failed</span>';
                    $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="verify(\'' . $subset['id'] . '\')">Verify Email</a>';
                    $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                    break;
                case ACTIONED:
                    $dt_subset['verified'] = '<span class="badge badge-success">Verified</span>';
                    $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                    break;
                case PENDING:
                default:
                    $dt_subset['verified'] = '<span class="badge badge-info">Pending Verification</span>';
                    $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="verify(\'' . $subset['id'] . '\')">Resend Verification</a>';
                    $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="check_verify_status()">Refresh Status</a>';
                    $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                    break;
            }

            array_push($dt_data['data'], $dt_subset);
        }

        echo json_encode($dt_data);
    }

    public function ajax_email_accounts_dt_get_row()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        $dataset = $this->dashboard_user_model->dt_get_email_address_accounts_summary(null, '', 0, 1, "WHERE tbl_partner_agents_email_address.id = " . $this->db->escape($this->input->post('row_id')));

        if (count($dataset) <= 0) {
            echo json_encode([]);
            return;
        }

        $dt_subset = $subset = $dataset[0];

        $dt_subset['DT_RowId'] = "row_" . $subset['id'];
        $dt_subset['DT_RowClass'] = "email-summary-row";
        $dt_subset['DT_RowAttr'] = ['attr-email-address' => $subset['email_address']];

        switch ($subset['verified']) {
            case FAILED:
                $dt_subset['verified'] = '<span class="badge badge-danger">Failed</span>';
                $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="verify(\'' . $subset['id'] . '\')">Verify Email</a>';
                $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                break;
            case ACTIONED:
                $dt_subset['verified'] = '<span class="badge badge-success">Verified</span>';
                $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                break;
            case PENDING:
            default:
                $dt_subset['verified'] = '<span class="badge badge-info">Pending Verification</span>';
                $dt_subset['actions'] = '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="verify(\'' . $subset['id'] . '\')">Resend Verification</a>';
                $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white" href="javascript:void(0);" onclick="check_verify_status()">Refresh Status</a>';
                $dt_subset['actions'] .= '<a class="btn btn-sm m-r-xs  btn-white text-danger" href="javascript:void(0);" onclick="remove(\'' . $subset['id'] . '\')">Delete</a>';
                break;
        }

        echo json_encode($dt_subset);
    }

    public function ajax_email_accounts_email_address_verify()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        if ((int) $user_profile['verified'] === STATUS_NG || $user_profile['role'] != USER_ADMIN) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        $partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
        if (count($partner_data) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        //form data
        $email_id = $this->input->post('email_id');
        $email_address = $this->input->post('email_address');
        if (empty($email_address)) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        //start session
        $this->db->trans_begin();

        if (empty($email_id)) {
            //new
            $tmp_data = [];
            $tmp_data['agent_id'] = $partner_data['super_agent'];
            $tmp_data['email_address'] = $email_address;
            $tmp_data['verified'] = PENDING;

            $tmp_result = $this->agent_model->set_agent_email_address_identities_info($tmp_data);
            if ($tmp_result === false || $tmp_result === -1) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_600]);
                return;
            }
        } else {
            //existing
            //update status back to PENDING
            if ($this->agent_model->set_agent_email_address_identities_info(['verified' => PENDING], $email_id) === false) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_601]);
                return;
            }
        }

        //verify via ses
        //NOTE: although the user can be a USER_ADMIN, were going to use the "agent" template so it redirects back to profile page
        $template = $this->config->item('mm8_product_code') . "_" . $this->config->item('mm8_system_prefix') . "_agent_email_" . ENVIRONMENT;
        $results = $this->aws_ses_library->send_custom_verification_email($email_address, $template);
        if (!$results['successful']) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_601]);
            return;
        }


        //commit
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_601]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
    }

    public function ajax_email_accounts_email_address_remove()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $this->account_manager_library->access_log();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        if ((int) $user_profile['verified'] === STATUS_NG || $user_profile['role'] != USER_ADMIN ) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        $partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
        if (count($partner_data) <= 0) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        //form data
        $email_id = $this->input->post('email_id');
        if (empty($email_id)) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        //get email identity
        $email_identity = $this->agent_model->get_agent_email_address_identities_info($email_id);
        if (empty($email_identity)) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        //start session
        $this->db->trans_begin();

        //reset ops email address?
        if ($user_profile['default_from_email'] == $email_identity['email_address'] || $user_profile['default_reply_to_email'] == $email_identity['email_address']) {
            $new_data = [];
            $new_data['default_from_email'] = $user_profile['default_from_email'] == $email_identity['email_address'] ? $partner_data['default_ops_email'] : $user_profile['default_from_email'];
            $new_data['default_reply_to_email'] = $user_profile['default_reply_to_email'] == $email_identity['email_address'] ? $partner_data['default_ops_email'] : $user_profile['default_reply_to_email'];

            if (!$this->agent_model->set_agent_info($new_data, $user_profile['id'])) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => ERROR_512]);
                return;
            }
        }

        //delete from list
        if (!$this->agent_model->delete_agent_email_address_identities($email_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_512]);
            return;
        }

        //delete identity from ses
        $results = $this->aws_ses_library->delete_email_identity($email_identity['email_address']);
        if (!$results['successful']) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_512]);
            return;
        }


        //commit
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => ERROR_512]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
    }

    public function ajax_email_accounts_load_modal()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['html_str' => '']);
            return;
        }

        $this->account_manager_library->access_log();

        echo json_encode(['html_str' => $this->load->view('commons/section_verify_email_address_modal', '', true)]);
    }

    public function email_ses_verification_hook()
    {
        $this->validate_access();

        //redirect to view
        $this->session->set_flashdata('modal_message', "The verification status of your email address may take a few minutes to update. Click on the 'Refresh Status' action button to refresh.");
        redirect(base_url() . 'profile/email/accounts', 'refresh');
    }

    /**
     *
     * Utilities
     *
     */
    protected function process_input_file($id)
    {
        if (isset($_FILES[$id]['tmp_name']) && file_exists($_FILES[$id]['tmp_name'])) {
            $file_mime_type = mime_content_type($_FILES[$id]['tmp_name']);

            $allowedFileTypes = [
                // Images
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/gif',
            ];

            //check file type
            if (!in_array($file_mime_type, $allowedFileTypes)) {
                return ['successful' => false, 'error' => "Invalid file type. Make sure the image is either a JPEG, PNG or GIF."];
            }

            //check file size
            if (filesize($_FILES[$id]['tmp_name']) > 2000000) {
                return ['successful' => false, 'error' => "File too large. Make sure the image is not more than 2 MB."];
            }

            //generate random filename
            $img_file = getRandomAlphaNum() . "." . pathinfo($_FILES[$id]['name'], PATHINFO_EXTENSION); //date("dHis") . '-' . pathinfo($_FILES[$id]['name'], PATHINFO_BASENAME);
            $tmp_file = $this->absolute_dir . $img_file;
            if (!move_uploaded_file($_FILES[$id]['tmp_name'], $tmp_file) || !file_exists($tmp_file)) {
                return ['successful' => false, 'error' => "Error uploading image file. Try again."];
            }

            //save file to s3
            if (ENVIRONMENT == "production") {
                $s3_file_url = $this->aws_s3_library_public->put_object($tmp_file, $this->relative_dir . $img_file);
                if (file_exists($this->relative_dir . $img_file)) {
                    unlink($this->relative_dir . $img_file);
                }
                if ($s3_file_url === false) {
                    return ['successful' => false, 'error' => "Error uploading image file. Try again."];
                }
            } else {
                $s3_file_url = base_url() . $this->relative_dir . $img_file;
            }

            return ['successful' => true, 'file' => $s3_file_url];
        } else {
            return ['successful' => true, 'file' => null];
        }
    }

    public function ajax_complete_address()
    {
        $term = $this->input->post('term');
        echo $this->kleber_datatools->search_address($term, 4);
    }

    public function ajax_parse_address()
    {
        echo $this->kleber_datatools->retrieve_address($this->input->post('id'), $this->input->post('value'));
    }

    /**
     *
     * Utils - Form validation Callbacks()
     *
     */
    public function user_email_available($email)
    {
        if ($this->dashboard_user_model->user_email_used($email, $this->session->utilihub_hub_user_role)) {
            $this->form_validation->set_message('user_email_available', ERROR_600);
            return false;
        } else {
            return true;
        }
    }

    public function change_user_email_available($email, $original_email)
    {
        if ($email != $original_email && $this->dashboard_user_model->user_email_used($email, $this->session->utilihub_hub_user_role)) {
            $this->form_validation->set_message('change_user_email_available', ERROR_600);
            return false;
        } else {
            return true;
        }
    }

    public function check_old_password($password, $reference_password)
    {
        if (crypt($password, $reference_password) != $reference_password) {
            $this->form_validation->set_message('check_old_password', 'Invalid password');
            return false;
        } else {
            return true;
        }
    }

    public function check_password_complexity($password)
    {
        $lengthOk = strlen($password) >= $this->config->item('mm8_system_password_length') ? true : false;
        $containsLower = preg_match('/[a-z]/', $password);
        $containsUpper = preg_match('/[A-Z]/', $password);
        $containsDigit = preg_match('/\d/', $password);
        $containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

        if ($lengthOk && $containsLower && $containsUpper && ($containsDigit || $containsSpecial)) {
            return true;
        } else {
            $this->form_validation->set_message('check_password_complexity', 'The password does not meet the password policy requirements.');
            return false;
        }
    }

    public function personalise_wizard()
    {
        $this->validate_access();

        //this ensures the user is reset to its main workspace
        //before loading page
        $dt_params = $this->input->get();
        if (isset($dt_params['home'])) {
            $url = base_url() . 'login/touch-base/?caller=' . $this->encryption->url_encrypt(current_url());
            redirect($url, 'refresh');
        }


        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        if ($user_profile === false || count($user_profile) <= 0) {
            $this->load->view('errors/restricted_page');
            return;
        }

        if ((int) $user_profile['verified'] === STATUS_NG) {
            redirect('login', 'refresh');
        }


        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "main_profile";
        $view_data['main_navigator_title'] = $this->main_navigator_title;

        if (!empty($this->partner_data)) {
            $view_data['package_type'] = $this->partner_data['package_type'];
            $view_data['partner_active'] = $this->partner_data['active'];
        }

        if (!empty($this->agent_data)) {
            $view_data['agent_active'] = $this->agent_data['active'];
        }

        if (!empty($this->accessible_modules)) {
            $view_data['accessible_modules'] = $this->accessible_modules;
        }


        /**
         * get the steps data
         */
        $personalise_wizard_steps = $this->agent_model->get_personalize_wizard_steps($user_profile['role']);
        $personalise_wizard_current_step_number = !empty($this->input->get('step')) ? $this->input->get('step') : 1;
        $personalise_wizard_current_step_number = $personalise_wizard_current_step_number > 0 ? $personalise_wizard_current_step_number : 1;
        $personalise_wizard_current_step_name = isset($personalise_wizard_steps[$personalise_wizard_current_step_number - 1]) ? $personalise_wizard_steps[$personalise_wizard_current_step_number - 1] : $personalise_wizard_steps[0];
        $personalise_wizard_field_mappings = $this->agent_model->get_personalize_wizard_field_label_mappings();
        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
            asset_url() . 'css/plugins/cropper/cropper.css',
            asset_url() . 'css/hub-profile-personalise-wizard.css'
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/cropper/cropper.js',
            asset_url() . 'js/plugins/cropper/jquery-cropper.js',
            asset_url() . 'js/hub-profile-personalise-wizard.js',
            asset_url() . 'js/hub-profile-personalise-wizard-' . $personalise_wizard_current_step_name . '.js'
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "init_load()";
        $view_data["user_profile"] = $user_profile;

        //kb explainer?
        $kb_code = 'main_profile';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }


        $additional_data = [];

        if ($user_profile['role'] == USER_ADMIN) {
            $additional_data['partner_data'] = $this->partner_model->get_partner_info($this->session->utilihub_hub_active_partner_id);
            if (count($additional_data['partner_data']) <= 0) {
                $this->load->view('errors/restricted_page');
                return;
            }

            //list of email accounts
            $tmp_lookup_email_accounts = $this->agent_model->get_agent_email_address_identities_list($this->session->utilihub_hub_user_id, true);
            if (!empty($additional_data['partner_data']['default_ops_email'])) {
                array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['default_ops_email']);
            }
            if (!empty($additional_data['partner_data']['ops_email'])) {
                array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['ops_email']);
            }
            if (!empty($additional_data['partner_data']['ops_email_reply_to'])) {
                array_unshift($tmp_lookup_email_accounts, $additional_data['partner_data']['ops_email_reply_to']);
            }
            if (!empty($user_profile['default_from_email'])) {
                array_unshift($tmp_lookup_email_accounts, $user_profile['default_from_email']);
            }
            if (!empty($user_profile['default_reply_to_email'])) {
                array_unshift($tmp_lookup_email_accounts, $user_profile['default_reply_to_email']);
            }

            $additional_data['lookup_email_accounts'] = array_unique($tmp_lookup_email_accounts);
        }

        //unsubscribe
        $additional_data['email_group_data'] = $this->subscription_model->get_email_groups_for_partner_agent_role($user_profile['role']);
        $additional_data['subscription_data'] = $this->subscription_model->get_subscription_for_partner_agent($this->session->utilihub_hub_user_id);

        $additional_data['profile_completeness'] = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);
        $additional_data['personalise_wizard_steps'] = $personalise_wizard_steps;
        $additional_data['personalise_wizard_current_step_number'] = $personalise_wizard_current_step_number;
        $additional_data['personalise_wizard_current_step_name'] = $personalise_wizard_current_step_name;
        $additional_data['personalise_wizard_field_mappings'] = $personalise_wizard_field_mappings;

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', $view_data);
        $this->load->view('profile/personalise_wizard', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    // personalise wizard

    public function ajax_profile_photo_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        $profile_photo = self::process_input_file('profile_photo');
        if (!$profile_photo['successful']) {
            echo json_encode(['successful' => false, 'error' => $profile_photo['error']]);
            return;
        }


        if (isset($profile_photo['file']) && !empty($profile_photo['file'])) {
            $user_data['profile_photo'] = $profile_photo['file'];
        } else {
            echo json_encode(['successful' => false, 'error' => "Profile photo file required"]);
            return;
        }


        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

        //update session
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->session->utilihub_hub_user_profile_photo = isset($user_profile['profile_photo']) && !empty($user_profile['profile_photo']) ? $user_profile['profile_photo'] : asset_url() . "img/default/profile-photo.jpg";
        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        echo json_encode(['successful' => true]);
    }

    public function ajax_profile_position_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        //SAVE DATA
        $user_data = [];
        $user_data['position'] = $dataset['position'];

        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

        //update session
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        echo json_encode(['successful' => true]);
    }

    public function ajax_profile_about_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        //SAVE DATA
        $user_data = [];
        $user_data['about'] = $dataset['about'];

        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

        //update session
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        echo json_encode(['successful' => true]);
    }

    public function ajax_profile_description_save()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //ACTION
        $dataset = $this->input->post();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        //SAVE DATA
        $user_data = [];
        $user_data['description'] = $dataset['description'];

        //START
        $this->db->trans_begin();

        //save
        if (!$this->dashboard_user_model->set_user_profile($user_data, $this->session->utilihub_hub_user_id)) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }


        //update children profiles (if there are any)
        if (!$this->dashboard_user_model->update_children_profile($this->session->utilihub_hub_user_id)) {
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

        //update session
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_profile === false || count($user_profile) <= 0) {
            echo json_encode(['successful' => false, 'error' => "Profile update failed! (ERROR_502)"]);
            return;
        }

        $this->session->utilihub_hub_user_profile_completeness = $this->agent_model->check_profile_completeness($this->session->utilihub_hub_user_id);

        echo json_encode(['successful' => true]);
    }

    public function preview_agent_microsite_profile()
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        $view_data = [];
        $view_data['user_profile'] = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);
        $this->load->view('partner/microsite/modals/preview_agent_profile', $view_data);
    }

    public function ajax_request_wallet_payout()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $this->account_manager_library->access_log();

        //get user profile
        $user_profile = $this->dashboard_user_model->get_user_profile($this->session->utilihub_hub_user_id);

        $details_html = "<p>";
        $details_html .= "Name: " . $user_profile['full_name'] . "<br>";
        $details_html .= "Email: " . $user_profile['email'] . "<br>";
        $details_html .= "Role: " . $this->config->item('mm8_agent_roles')[$user_profile['role']] . "<br>";

        //get email template
        $template = $this->communications_model->get_email_template('ams_request_for_wallet_payout');
        if (!$template) {
            echo json_encode(['successful' => false, 'error' => ERROR_512]);
            return;
        }


        //IDENTIFY THE IS
        $account_manager_profile = [];
        switch ($this->session->utilihub_hub_user_role) {
            case USER_ADMIN:
                $partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_partner_id);
                if (!$partner_data) {
                    echo json_encode(['successful' => false, 'error' => ERROR_512]);
                    return;
                }

                $account_manager = $this->account_manager_model->getById($partner_data['internal_account_manager_id']);
                if (!$account_manager) {
                    echo json_encode(['successful' => false, 'error' => ERROR_512]);
                    return;
                }

                $account_manager_profile = $this->dashboard_user_model->get_user_profile($account_manager->account_manager_agent);
                if (!$account_manager_profile) {
                    echo json_encode(['successful' => false, 'error' => ERROR_512]);
                    return;
                }

                $details_html .= "Workspace: " . $partner_data['name'] . " (" . $partner_data['reference_code'] . ")<br>";
                $details_html .= "</p>";

                //SEND EMAIL FOR NOW
                $search_for = [
                    "[NAME]",
                    "[DETAILS]"
                ];
                $replace_with = [
                    $account_manager_profile['first_name'],
                    $details_html
                ];

                $html_template = str_replace($search_for, $replace_with, $template['html_template']);
                $text_template = str_replace($search_for, $replace_with, $template['text_template']);

                $email_data = ['partner_id' => $partner_data['id'], 'is_read' => 0];
                $email_id = $this->account_manager_log_email_model->save($email_data);
                if ($email_id === false || $email_id === -1) {
                    echo json_encode(['successful' => false, 'error' => ERROR_502]);
                    return;
                }

                $from_name = $user_profile['full_name'];
                $from = $partner_data['ops_email'];
                $reply_to = $partner_data['ops_email_reply_to'];
                $to = $account_manager_profile['email'];

                $email_data = $this->email_library->send_ams_email($email_id, 0, 0, $partner_data['id'], 0, $from_name, $from, $reply_to, $to, null, null, $template['subject'], $html_template, "", true, true);
                if (!$email_data['status']) {
                    echo json_encode(['successful' => false, 'error' => "Error sending email"]);
                    return;
                }

                $email_data['processed'] = STATUS_OK;
                $email_data['date_processed'] = $this->database_tz_model->now();
                $email_id = $this->account_manager_log_email_model->save($email_data);
                if ($email_id === false || $email_id === -1) {
                    echo json_encode(['successful' => false, 'error' => ERROR_502]);
                    return;
                }

                break;

            default:
                echo json_encode(['successful' => false, 'error' => ERROR_512]);
                return;
        }


        $this->session->utilihub_hub_request_payout_btn_enabled = false;
        echo json_encode(['successful' => true]);
    }
}
