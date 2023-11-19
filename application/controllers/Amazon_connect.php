<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Amazon_connect extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('users_model');
    }

    public function ajax_is_login()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        if ($user['amazon_connect_is_logged_in'] == STATUS_OK) {
            echo json_encode(['successful' => true]);
            return;
        } else {
            echo json_encode(['successful' => false]);
            return;
        }
        echo json_encode(['successful' => true]);
    }

    public function window_open_amazon_connect_stream()
    {
        if (!$this->session->utilihub_hub_session) {
            show_404();
        }

        $user = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        /**
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];

        /**
         *
         * DEFINE CURRENT PAGE IN MENU
         *
         */
        $view_data['user_menu'] = "";

        // everybody should be able to edit their own profile
        /* if (!isset($view_data['user_acl_whitelist'][$view_data['user_menu']])) {
          $this->load->view('errors/restricted_page');
          return;
          } */

        /**
         *
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [];

        $view_data['scripts'] = [
            asset_url() . 'js/plugins/slimscroll/jquery.slimscroll.min.js',
        ];

        /**
         *
         * DEFINE ADDITIONAL STYLING FOR <BODY>
         * ALSO SETS IF MAIN MENU IS COLLAPSED OR NOT
         *
         */

        /**
         *
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        //$view_data['onload_call'] = "init_load()";

        $this->load->view('amazon_connect/template_popup_header', $view_data);
        $this->load->view('amazon_connect/window_open_amazon_connect_stream');
        $this->load->view('amazon_connect/template_popup_footer', $view_data);
    }

    /*
     *
     * Agent Events
     *
     */

    public function ajax_agent_state_change()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $oldState = isset($dataset['oldState']) ? $dataset['oldState'] : null;
        $newState = isset($dataset['newState']) ? $dataset['newState'] : null;

        switch ($newState) {
            case 'Training':
     
                $data = [
                    'amazon_connect_is_logged_in' => STATUS_OK,
                ];
                $this->users_model->set_user_profile($data, $user['id']);

                break;
            case 'Meeting':
                $data = [
                    'amazon_connect_is_logged_in' => STATUS_OK,
                ];
                $this->users_model->set_user_profile($data, $user['id']);

                break;
            case 'Break':

                $data = [
                    'amazon_connect_is_logged_in' => STATUS_OK,
                ];
                $this->users_model->set_user_profile($data, $user['id']);

                break;
            case 'Available':

                $data = [
                    'amazon_connect_is_logged_in' => STATUS_OK,
                ];
                $this->users_model->set_user_profile($data, $user['id']);

                break;
            case 'Offline':

                $data = [
                    'amazon_connect_is_logged_in' => STATUS_OK,
                ];
                $this->users_model->set_user_profile($data, $user['id']);
                break;
            default:
        }

        echo json_encode(['successful' => true]);
    }

    /*
     *
     * Agent Events
     *
     */

    public function ajax_agent_init()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }


        if (!$this->session->utilihub_user_amazon_connect_ccp_init) {
            $this->session->utilihub_user_amazon_connect_ccp_init = true;

            echo json_encode(['successful' => true]);
            return;
        }

        echo json_encode(['successful' => false]);
    }

    /*
     *
     * Agent Events
     * Used for allocating play
     * So if Agent is Busy or on call, do not allocated
     *
     */

    public function ajax_agent_update_current_status()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $oldState = isset($dataset['oldState']) ? $dataset['oldState'] : null;
        $newState = isset($dataset['newState']) ? $dataset['newState'] : null;

        $this->db->trans_begin();

        $data = [
            'amazon_connect_current_status' => $newState,
        ];
        $this->users_model->set_user_profile($data, $user['id']);

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
    }

    public function ajax_agent_status_logout()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        //user tracking
        if ($this->session->mm8_mhub_feature_agent_tracking && $this->session->has_userdata('utilihub_user_tracking_activity_id')) {
            //close off old status
            $this->users_model->end_tracking($this->session->utilihub_user_tracking_activity_id);
        }

        $this->db->trans_begin();

        $data = [
            'amazon_connect_is_logged_in' => STATUS_NG,
        ];
        $this->users_model->set_user_profile($data, $user_profile['id']);

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        }

        $this->db->trans_commit();

        $this->clear_session();

        echo json_encode(['successful' => true]);
    }

    protected function clear_session()
    {
        $this->session->sess_destroy();

        if ($this->config->item('mm8_mhub_feature_utilichat') == STATUS_OK) {
            delete_cookie("ccuserid");
        }
    }

    public function ajax_redirect_to_application()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        $application = $this->application_model->getByAwsAmazonConnectQueueContactid($contactId);
        if ($application) {
            if ($application->partner_id) {
                $partner = $this->partner_model->get_partner_info($application->partner_id);

                $this->db->trans_begin();

                $data = [
                    'application_id' => $application->id,
                    'user_id' => $user_profile['id'],
                ];
                $application_queue_id = $this->user_amazon_connect_application_queues_model->save($data);
                if (!$application_queue_id) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => ERROR_408]);
                    return;
                }

                if ($this->db->trans_status() === false) {
                    $this->db->trans_rollback();
                    echo json_encode(['successful' => false, 'error' => ERROR_408]);
                    return;
                }

                $this->db->trans_commit();

                echo json_encode(['successful' => true, 'url' => base_url() . "connect/view/" . $partner['reference_code'] . "/" . $application->id]);
                return;
            }
        }

        echo json_encode(['successful' => false]);
    }

    public function ajax_contact_on_missed()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        echo json_encode(['successful' => true]);
    }

    public function ajax_contact_on_ended()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        echo json_encode(['successful' => true]);
    }

    public function ajax_open_new_window()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        $contact = $this->amazon_connect_contacts_model->getByContactId($contactId);
        if ($contact) {
            if (!empty($contact->application_id)) {
                $application = $this->application_model->get_application_customer_info($contact->application_id);
                if ($application) {
                    $this->db->trans_begin();

                    $data = [
                        'id' => $application['app_id'],
                        'amazon_connect_is_live' => 1,
                        'amazon_connect_is_live_date' => $this->database_tz_model->now(),
                        'amazon_connect_last_contact_id' => $contactId,
                    ];
                    $application_id = $this->application_model->save($data);
                    if (!$application_id) {
                        $this->db->trans_rollback();
                        echo json_encode(['successful' => false, 'error' => ERROR_408]);
                        return;
                    }

                    //COMMIT
                    if ($this->db->trans_status() === false) {
                        $this->db->trans_rollback();
                    }

                    $this->db->trans_commit();

                    // specific application
                    $partner_data = $this->partner_model->get_partner_info($application['partner_id']);
                    if (count($partner_data) > 0) {
                        // Restrict User Access to Edit Applications in Reviewing Status
                        if ($user_profile['role'] == MHUB_AGENT && $application['application_status'] == REVIEWING) {
                            echo json_encode(['successful' => true, 'url' => base_url() . "connect/view/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                            return;
                        } else {
                            if ($application['user_locked'] == $user_profile['id'] || empty($application['user_locked']) || is_null($application['user_locked'])) {
                                echo json_encode(['successful' => true, 'url' => base_url() . "connect/update/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                                return;
                            } else {
                                echo json_encode(['successful' => true, 'url' => base_url() . "connect/view/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                                return;
                            }
                        }
                    } else {
                        echo json_encode(['successful' => false]);
                        return;
                    }
                } else {
                    // Hard coded test for mobile number
                    // $customerEndpointAddressToLocal = $contact->customer_endpoint_address;

                    $customerEndpointAddressToLocal = $this->sms_library_base->convert_e164_to_local($contact->customer_endpoint_address);

                    // check if there is a customer phone number with application
                    $filter = [
                        'primary_phone' => $customerEndpointAddressToLocal,
                    ];
                    $customerWithApplications = $this->customer_model->getCustomerApplications($filter);
                    if (count($customerWithApplications) > 0) {
                        // open customers
                        echo json_encode([
                            'successful' => true,
                            'customer_endpoint_address' => $contact->customer_endpoint_address,
                            'url' => base_url() . "customers/view-by-phone-number/" . $this->encryption->url_encrypt($customerEndpointAddressToLocal)]);
                        return;
                    } else {
                        echo json_encode([
                            'successful' => false,
                        ]);
                        return;
                    }
                }
            } else {
                // Hard coded test for mobile number
                // $customerEndpointAddressToLocal = $contact->customer_endpoint_address;

                $customerEndpointAddressToLocal = $this->sms_library_base->convert_e164_to_local($contact->customer_endpoint_address);

                // check if there is a customer phone number with application
                $filter = [
                    'primary_phone' => $customerEndpointAddressToLocal,
                ];
                $customerWithApplications = $this->customer_model->getCustomerApplications($filter);
                if (count($customerWithApplications) > 1) {
                    // open customers
                    echo json_encode([
                        'successful' => true,
                        'customer_endpoint_address' => $contact->customer_endpoint_address,
                        'url' => base_url() . "customers/view-by-phone-number/" . $this->encryption->url_encrypt($customerEndpointAddressToLocal)]);
                    return;
                } else {
                    echo json_encode([
                        'successful' => false,
                    ]);
                    return;
                }
            }
        } else {
            // check previous maybe this call coming from callback
            try {
                $client = new Aws\Connect\ConnectClient([
                    'version' => '2017-08-08',
                    'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                    'credentials' => [
                        'key' => $this->config->item('mm8_aws_access_key_id'),
                        'secret' => $this->config->item('mm8_aws_secret_access_key'),
                    ],
                ]);

                $result = $client->describeContact([
                    'ContactId' => $contactId, // REQUIRED
                    'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
                ]);

                if (isset($result['Contact']['InitialContactId']) && !empty($result['Contact']['InitialContactId'])) {
                    $contactId = $result['Contact']['InitialContactId'];
                    $contact = $this->amazon_connect_contacts_model->getByContactId($contactId);
                    if ($contact) {
                        if (!empty($contact->application_id)) {
                            $application = $this->application_model->get_application_customer_info($contact->application_id);
                            if ($application) {
                                $this->db->trans_begin();

                                $data = [
                                    'id' => $application['app_id'],
                                    'amazon_connect_is_live' => 1,
                                    'amazon_connect_is_live_date' => $this->database_tz_model->now(),
                                    'amazon_connect_last_contact_id' => $contactId,
                                ];
                                $application_id = $this->application_model->save($data);
                                if (!$application_id) {
                                    $this->db->trans_rollback();
                                    echo json_encode(['successful' => false, 'error' => ERROR_408]);
                                    return;
                                }

                                //COMMIT
                                if ($this->db->trans_status() === false) {
                                    $this->db->trans_rollback();
                                }

                                $this->db->trans_commit();

                                // specific application
                                $partner_data = $this->partner_model->get_partner_info($application['partner_id']);
                                if (count($partner_data) > 0) {
                                    // Restrict User Access to Edit Applications in Reviewing Status
                                    if ($user_profile['role'] == MHUB_AGENT && $application['application_status'] == REVIEWING) {
                                        echo json_encode(['successful' => true, 'url' => base_url() . "connect/view/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                                        return;
                                    } else {
                                        if ($application['user_locked'] == $user_profile['id'] || empty($application['user_locked']) || is_null($application['user_locked'])) {
                                            echo json_encode(['successful' => true, 'url' => base_url() . "connect/update/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                                            return;
                                        } else {
                                            echo json_encode(['successful' => true, 'url' => base_url() . "connect/view/" . $partner_data['reference_code'] . "/" . $application['app_id']]);
                                            return;
                                        }
                                    }
                                } else {
                                    echo json_encode(['successful' => false]);
                                    return;
                                }
                            } else {
                                // Hard coded test for mobile number
                                // $customerEndpointAddressToLocal = $contact->customer_endpoint_address;

                                $customerEndpointAddressToLocal = $this->sms_library_base->convert_e164_to_local($contact->customer_endpoint_address);

                                // check if there is a customer phone number with application
                                $filter = [
                                    'primary_phone' => $customerEndpointAddressToLocal,
                                ];
                                $customerWithApplications = $this->customer_model->getCustomerApplications($filter);
                                if (count($customerWithApplications) > 0) {
                                    // open customers
                                    echo json_encode([
                                        'successful' => true,
                                        'customer_endpoint_address' => $contact->customer_endpoint_address,
                                        'url' => base_url() . "customers/view-by-phone-number/" . $this->encryption->url_encrypt($customerEndpointAddressToLocal)]);
                                    return;
                                } else {
                                    echo json_encode([
                                        'successful' => false,
                                    ]);
                                    return;
                                }
                            }
                        } else {
                            // Hard coded test for mobile number
                            // $customerEndpointAddressToLocal = $contact->customer_endpoint_address;

                            $customerEndpointAddressToLocal = $this->sms_library_base->convert_e164_to_local($contact->customer_endpoint_address);

                            // check if there is a customer phone number with application
                            $filter = [
                                'primary_phone' => $customerEndpointAddressToLocal,
                            ];
                            $customerWithApplications = $this->customer_model->getCustomerApplications($filter);
                            if (count($customerWithApplications) > 1) {
                                // open customers
                                echo json_encode([
                                    'successful' => true,
                                    'customer_endpoint_address' => $contact->customer_endpoint_address,
                                    'url' => base_url() . "customers/view-by-phone-number/" . $this->encryption->url_encrypt($customerEndpointAddressToLocal)]);
                                return;
                            } else {
                                echo json_encode([
                                    'successful' => false,
                                ]);
                                return;
                            }
                        }
                    }
                }

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            } catch (ConnectException $e) {
                // echo $e->getAwsRequestId() . "\n";
                // echo $e->getAwsErrorType() . "\n";
                // echo $e->getAwsErrorCode() . "\n";
                // echo $e->getMessage();

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            } catch (AwsException $e) {
                // echo $e->getAwsRequestId() . "\n";
                // echo $e->getAwsErrorType() . "\n";
                // echo $e->getAwsErrorCode() . "\n";
                // echo $e->getMessage();

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            }
        }

        echo json_encode(['successful' => false]);
        return;
    }

    public function ajax_display_status()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        $contact = $this->amazon_connect_contacts_model->getByContactId($contactId);
        if ($contact) {
            if (!empty($contact->application_id)) {
                $application = $this->application_model->get_application_customer_info($contact->application_id);
                if ($application) {
                    $view_data = [];
                    $view_data['application'] = $application;
                    $view_data['partner'] = $this->partner_model->get_partner_info($application['partner_id']);

                    $html_str = $this->load->view('amazon_connect/ccp_display_status', $view_data, true);

                    echo json_encode([
                        'successful' => true,
                        'html' => $html_str,
                    ]);
                    return;
                }
            }
        } else {
            // check previous maybe this call coming from callback
            try {
                $client = new Aws\Connect\ConnectClient([
                    'version' => '2017-08-08',
                    'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                    'credentials' => [
                        'key' => $this->config->item('mm8_aws_access_key_id'),
                        'secret' => $this->config->item('mm8_aws_secret_access_key'),
                    ],
                ]);

                $result = $client->describeContact([
                    'ContactId' => $contactId, // REQUIRED
                    'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
                ]);

                if (isset($result['Contact']['InitialContactId']) && !empty($result['Contact']['InitialContactId'])) {
                    $contactId = $result['Contact']['InitialContactId'];
                    $contact = $this->amazon_connect_contacts_model->getByContactId($contactId);
                    if ($contact) {
                        if (!empty($contact->application_id)) {
                            $application = $this->application_model->get_application_customer_info($contact->application_id);
                            if ($application) {
                                $view_data = [];
                                $view_data['application'] = $application;
                                $view_data['partner'] = $this->partner_model->get_partner_info($application['partner_id']);

                                $html_str = $this->load->view('amazon_connect/ccp_display_status', $view_data, true);

                                echo json_encode([
                                    'successful' => true,
                                    'html' => $html_str,
                                ]);
                                return;
                            }
                        }
                    }
                }

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            } catch (ConnectException $e) {
                // echo $e->getAwsRequestId() . "\n";
                // echo $e->getAwsErrorType() . "\n";
                // echo $e->getAwsErrorCode() . "\n";
                // echo $e->getMessage();

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            } catch (AwsException $e) {
                // echo $e->getAwsRequestId() . "\n";
                // echo $e->getAwsErrorType() . "\n";
                // echo $e->getAwsErrorCode() . "\n";
                // echo $e->getMessage();

                echo json_encode([
                    'successful' => false,
                ]);
                return;
            }
        }

        echo json_encode(['successful' => false]);
        return;
    }

    public function ajax_check_application_is_live()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $filter = [
            'user_assigned' => $this->session->utilihub_hub_user_id,
            'amazon_connect_is_live' => STATUS_OK,
        ];
        $order = [
            'amazon_connect_is_live_date',
        ];
        $application = $this->application_model->fetch($filter, $order, 1);
        if (count($application) > 0) {
            if ($application[0]->partner_id) {
                $partner = $this->partner_model->get_partner_info($application[0]->partner_id);
                if ($partner) {
                    echo json_encode([
                        'successful' => true,
                        'application_id' => $application[0]->id,
                        'application_reference' => $application[0]->reference_code,
                        'url' => base_url() . "connect/update/" . $partner['reference_code'] . "/" . $application[0]->id
                    ]);
                    return;
                }
            }
        }

        echo json_encode(['successful' => false]);
        return;
    }

    public function voicemail_record_download($recordingLocation)
    {
        $this->load->helper('download');

        $recordingLocation = $this->encryption->url_decrypt($recordingLocation);

        try {
            $client = new Aws\S3\S3Client([
                'version' => 'latest',
                'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key' => $this->config->item('mm8_aws_access_key_id'),
                    'secret' => $this->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $bucket = $this->config->item('mm8_amazon_connect_S3_bucket_reports');
            $filename = str_replace($this->config->item('mm8_amazon_connect_S3_bucket_reports') . "/", '', $recordingLocation);
            if ($client->doesObjectExist($bucket, $filename)) {
                // $recordingLocationUrl = $client->getObjectUrl($bucket, $filename);

                $client->registerStreamWrapper();

                $contents = '';
                if ($stream = fopen('s3://' . $bucket . '/' . $filename, 'r')) {
                    // While the stream is still open
                    while (!feof($stream)) {
                        // Read 1,024 bytes from the stream
                        $contents .= fread($stream, 1024);
                    }
                    // Be sure to close the stream resource when you're done with it
                    fclose($stream);
                }

                force_download(basename($filename), $contents);
            } else {
                echo "Voicemail Recording not found.";
                exit();
            }
        } catch (S3Exception $e) {
            echo "Error code: " . $e->getAwsErrorCode();
            echo "Error: " . $e->getMessage();
            exit();
        } catch (AwsException $e) {
            echo "Error code: " . $e->getAwsErrorCode();
            echo "Error: " . $e->getMessage();
            exit();
        }

        /*
          $exists = $this->aws_s3_library->does_object_exists($download_uri);
          if ($exists) {
          //echo basename($download_uri);
          $s3_file_data = $this->aws_s3_library->read_s3_stream($download_uri);
          force_download(basename($download_uri), $s3_file_data);
          }
         */
    }

    /*
    *
    * https://aws.amazon.com/blogs/contact-center/pausing-and-resuming-call-recordings-with-a-new-api-in-amazon-connect/
    *
    * https://docs.aws.amazon.com/connect/latest/APIReference/API_StartContactRecording.html
    * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-connect-2017-08-08.html#startcontactrecording
    *
     */
    public function ajax_recording_start()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        if (empty($contactId)) {
            echo json_encode(['successful' => false, 'error' => 'Required Field: Contact ID']);
        }

        try {
            $client = new Aws\Connect\ConnectClient([
                'version' => '2017-08-08',
                'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key' => $this->config->item('mm8_aws_access_key_id'),
                    'secret' => $this->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $result = $client->startContactRecording([
                'ContactId' => $contactId, // REQUIRED
                'InitialContactId' => $contactId, // REQUIRED
                'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
                'VoiceRecordingConfiguration' => [ // REQUIRED
                    'VoiceRecordingTrack' => 'ALL', // FROM_AGENT|TO_AGENT|ALL
                ],
            ]);

            echo json_encode(['successful' => true, 'result' => $result]);
            return;
        } catch (ConnectException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        } catch (AwsException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        }

        echo json_encode(['successful' => true]);
    }

    /*
    *
    * https://docs.aws.amazon.com/connect/latest/APIReference/API_StopContactRecording.html
    * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-connect-2017-08-08.html#stopcontactrecording
    *
     */
    public function ajax_recording_stop()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        if (empty($contactId)) {
            echo json_encode(['successful' => false, 'error' => 'Required Field: Contact ID']);
            return;
        }

        try {
            $client = new Aws\Connect\ConnectClient([
                'version' => '2017-08-08',
                'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key' => $this->config->item('mm8_aws_access_key_id'),
                    'secret' => $this->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $result = $client->stopContactRecording([
                'ContactId' => $contactId, // REQUIRED
                'InitialContactId' => $contactId, // REQUIRED
                'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
            ]);

            echo json_encode(['successful' => true, 'result' => $result]);
        } catch (ConnectException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        } catch (AwsException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        }
    }

    /*
    *
    * https://docs.aws.amazon.com/connect/latest/APIReference/API_SuspendContactRecording.html
    * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-connect-2017-08-08.html#suspendcontactrecording
    *
     */
    public function ajax_recording_pause()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        if (empty($contactId)) {
            echo json_encode(['successful' => false, 'error' => 'Required Field: Contact ID']);
            return;
        }

        try {
            $client = new Aws\Connect\ConnectClient([
                'version' => '2017-08-08',
                'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key' => $this->config->item('mm8_aws_access_key_id'),
                    'secret' => $this->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $result = $client->suspendContactRecording([
                'ContactId' => $contactId, // REQUIRED
                'InitialContactId' => $contactId, // REQUIRED
                'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
            ]);

            echo json_encode(['successful' => true, 'result' => $result]);
        } catch (ConnectException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        } catch (AwsException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        }
    }

    /*
    *
    * https://docs.aws.amazon.com/connect/latest/APIReference/API_ResumeContactRecording.html
    * https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-connect-2017-08-08.html#resumecontactrecording
    *
     */
    public function ajax_recording_resume()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $user_profile = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if (!$user_profile) {
            echo json_encode(['successful' => false, 'error' => ERROR_408]);
            return;
        }

        $dataset = $this->input->post();
        $contactId = isset($dataset['contactId']) ? $dataset['contactId'] : null;

        if (empty($contactId)) {
            echo json_encode(['successful' => false, 'error' => 'Required Field: Contact ID']);
            return;
        }

        try {
            $client = new Aws\Connect\ConnectClient([
                'version' => '2017-08-08',
                'region' => $this->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key' => $this->config->item('mm8_aws_access_key_id'),
                    'secret' => $this->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $result = $client->resumeContactRecording([
                'ContactId' => $contactId, // REQUIRED
                'InitialContactId' => $contactId, // REQUIRED
                'InstanceId' => $this->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
            ]);

            echo json_encode(['successful' => true, 'result' => $result]);
        } catch (ConnectException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        } catch (AwsException $e) {
            // echo $e->getAwsRequestId() . "\n";
            // echo $e->getAwsErrorType() . "\n";
            // echo $e->getAwsErrorCode() . "\n";
            // echo $e->getMessage();

            echo json_encode(['successful' => false, 'error' => $e->getMessage()]);
        }
    }
}
