<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    protected $_userType = null;
    protected $app = CONNECT_SD_APP_HUB;

    public function __construct()
    {
        parent::__construct();

        $this->load->model('communications_model');

        $this->load->library('form_validation');
        $this->load->library('email_library');
    }

    /**
     *
     * Login Main
     *
     */
    public function index()
    {
        if (count($this->input->post()) <= 0) {
            $this->load->view('login/main_page');
            $this->session->sess_destroy();
            return;
        }

        /**
         *
         * FORM ACTION (POST)
         *
         */
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');

        //1. validate email first
        $this->form_validation->set_rules('login_email', 'Email', 'trim|required|valid_email|callback_user_email_exist');
        if ($this->form_validation->run() == false) {
            $this->load->view('login/main_page');
            $this->session->sess_destroy();
            return;
        }

        $user_data = $this->users_model->get_user_login($this->input->post('login_email'));
        if (!$user_data) {
            $this->session->sess_destroy();
            redirect(current_url());
        }

        //2. validate the rest
        $this->form_validation->set_rules('login_password', 'Password', 'trim|required|callback_check_database[' . $user_data['id'] . ']');

        if ($this->form_validation->run() == false) {
            $this->load->view('login/main_page');
            $this->session->sess_destroy();
        } else {
            $redirect = $this->input->post('redirect');

            //REDIRECTING NOW...
            if (!empty($redirect)) {
                if (filter_var($redirect, FILTER_VALIDATE_URL)) {
                    if (strpos($redirect, $this->config->item('base_url')) !== false) {
                        redirect($redirect, 'refresh');
                    }
                }
            } else {
                redirect(base_url() . "dashboard", 'refresh');
            }
        }
    }

    /**
     *
     * Login - Google Auth
     *
     */
    public function google_auth()
    {
        //Step 1: Configure the client object
        $client = new Google_Client();
        $client->setAuthConfig(APPPATH . '/config/google_client_secrets.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->setRedirectUri(base_url() . 'login/google-auth-validate');

        //Step 2: Redirect to Google's OAuth 2.0 server
        $auth_url = $client->createAuthUrl();
        header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));

        //Step 3: Google prompts user for consent
        //Step 4: Handle the OAuth 2.0 server response
    }

    public function google_auth_validate()
    {
        $generic_g_error = "Something went wrong here. (Invalid Grant)";

        if ((int) $this->config->item('mm8_hub_account_google_sso') != STATUS_OK) {
            $this->session->set_flashdata('error_message', $generic_g_error);
            redirect(base_url() . 'register', 'refresh');
        }


        $client = new Google_Client();
        $client->setAuthConfig(APPPATH . '/config/google_client_secrets.json');
        $client->setAccessType("offline");        // offline access
        $client->setIncludeGrantedScopes(true);   // incremental auth
        $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
        $client->setRedirectUri(base_url() . 'login/google-auth-validate');

        if (!isset($_GET['code'])) {
            $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null, "agent_id" => null]));
            $this->session->set_flashdata('error_message', "Google Auth Error: Invalid code.");
            redirect(base_url() . 'login', 'refresh');
        } else {
            //Step 5: Exchange authorization code for refresh and access tokens
            $client->authenticate($_GET['code']);
            $tokens = $client->getAccessToken();

            if ($tokens && isset($tokens['access_token'])) {
                $curl = curl_init("https://www.googleapis.com/userinfo/v2/me");
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $tokens['access_token']]);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $curl_response = curl_exec($curl);
                curl_close($curl);
                $dataset = json_decode($curl_response, true);

                //BEGIN VALIDATION
                $user_data = $this->users_model->get_user_login($dataset['email']);
                if (!$user_data) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Invalid user data (1).");
                    redirect(base_url() . 'login', 'refresh');
                }

                //identify if there's more than one account.
                //if more thatn one, set to fail:
                //for now, we wont support GOOGLE AUTHENTICATION for multiple accounts
                if (!isset($user_data['id']) && count($user_data) > 1) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Multiple accounts found.");
                    redirect(base_url() . 'login', 'refresh');
                }



                $login_data = $this->users_model->get_user_profile($user_data['id']);
                if ($login_data === false || count($login_data) <= 0) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Invalid user data (2).");
                    redirect(base_url() . 'login', 'refresh');
                }


                //0. based on role. check user is allowed here
                if (!isset($this->config->item('hub_landing_page')[$login_data['role']])) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null, "email" => $login_data['email']]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Invalid user data (3).");
                    redirect(base_url() . 'login', 'refresh');
                }

                //1. check if user is active, confirmed and verified
                //check if user login method = USER_LOGIN_GOOGLEAUTH
                if ((int) $login_data['active'] === STATUS_NG || (int) $login_data['confirmed'] === STATUS_NG || (int) $login_data['verified'] === STATUS_NG || (int) $login_data['login_method'] != USER_LOGIN_GOOGLEAUTH) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null, "email" => $login_data['email']]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Invalid account.");
                    redirect(base_url() . 'login', 'refresh');
                }

                //4. check if google user_id is a match
                if ($dataset['id'] != $login_data['google_user_id']) {
                    $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => null, "email" => $login_data['email']]));
                    $this->session->set_flashdata('error_message', "Google Auth Error: Invalid id.");
                    redirect(base_url() . 'login', 'refresh');
                }


                //succesful
                $this->initialise_login($login_data);
                $this->users_model->log_audit_trail($login_data['id'], "login_successful", json_encode(["method" => USER_LOGIN_GOOGLEAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));

                //REDIRECTING NOW...
                if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
                    redirect(base_url() . 'login/account-suspended', 'refresh');
                }
                //redirect to default page based on role
                elseif (isset($this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role])) {
                    $this->session->utilihub_hub_landing_page = base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role];
                    redirect($this->session->utilihub_hub_landing_page, 'refresh');
                } else {
                    $this->session->sess_destroy();
                    $this->session->set_flashdata('error_message', $generic_g_error);
                    redirect(base_url() . 'login', 'refresh');
                }
            } else {
                $this->session->set_flashdata('error_message', "Google Auth Error: Invalid token.");
                redirect(base_url() . 'login', 'refresh');
            }
        }
    }

    /**
     *
     * Forgot / Reset Password
     *
     */
    public function request_reset()
    {
        if (count($this->input->post()) <= 0) {
            $this->load->view('login/request_reset_page');
            $this->session->sess_destroy();
            return;
        }


        /**
         *
         * FORM ACTION (POST)
         *
         */
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('login_email', 'Email', 'trim|required|valid_email');

        if ($this->form_validation->run() == false) {
            $this->load->view('login/request_reset_page');
            $this->session->sess_destroy();
            return;
        }


        $dataset = $this->users_model->get_user_login($this->input->post('login_email'));
        if (!$dataset) {
            //PCI requirement: weve removed the callback_user_email_exist() validation
            //dont flag an error message, instead, say email has been sent
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => $this->input->post('login_email')]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
            redirect(current_url());
        }

        //identify if there's more than one account.
        //if more thatn one, redirect to request-reset2
        if (!isset($dataset['id']) && count($dataset) > 1) {
            $this->session->sess_destroy();
            $token = $this->encryption->url_encrypt($this->input->post('login_email'));
            redirect(base_url() . 'login/request-reset2/' . $token, 'refresh');
        }



        //AT THIS POINT, THERES ONLY 1 ACCOUNT MATCHED
        //check if user is active, confirmed and verified
        //check if login_method = USER_LOGIN_BASICAUTH;
        if ((int) $dataset['active'] === STATUS_NG || (int) $dataset['confirmed'] === STATUS_NG || (int) $dataset['verified'] === STATUS_NG || (int) $dataset['login_method'] != USER_LOGIN_BASICAUTH) {
            //PCI requirement: weve removed the callback_user_email_exist() validation
            //dont flag an error message, instead, say email has been sent
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => $dataset['id'], "email" => $this->input->post('login_email')]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
            redirect(current_url());
        }

        //based on role. check user is allowed here
        if (!isset($this->config->item('hub_landing_page')[$dataset['role']])) {
            //PCI requirement: weve removed the callback_user_email_exist() validation
            //dont flag an error message, instead, say email has been sent
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => $dataset['id'], "email" => $this->input->post('login_email')]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
            redirect(current_url());
        }


        $this->process_reset_password_request($dataset);
    }

    public function request_reset2($token = null)
    {
        if (empty($token)) {
            $this->session->sess_destroy();
            redirect(base_url(), 'refresh');
        }


        $init_form_data = [];
        $init_form_data['token_str'] = $token;
        $init_form_data['login_email'] = $this->encryption->url_decrypt($token);
        $init_form_data['partners_list'] = $this->users_model->get_user_agent_partners_list($init_form_data['login_email']);

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/request_reset_page2', $init_form_data);
            $this->session->sess_destroy();
            return;
        }


        /**
         *
         * FORM ACTION (POST)
         *
         */
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('login_partner', 'Campaign', 'required');

        if ($this->form_validation->run() == false) {
            $this->load->view('login/request_reset_page2', $init_form_data);
            $this->session->sess_destroy();
            return;
        }


        $dataset = $this->users_model->get_user_agent_profile($init_form_data['login_email'], $this->input->post('login_partner'));

        //check if user is active, confirmed and verified
        //check if login_method = USER_LOGIN_BASICAUTH;
        if (!$dataset || (int) $dataset['active'] == STATUS_NG || (int) $dataset['confirmed'] == STATUS_NG || (int) $dataset['verified'] == STATUS_NG || (int) $dataset['login_method'] != USER_LOGIN_BASICAUTH) {
            //PCI requirement: weve removed the callback_user_email_exist() validation
            //dont flag an error message, instead, say email has been sent
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => $this->input->post('login_email'), "partner" => $this->input->post('login_partner')]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
            redirect(current_url());
        }

        //based on role. check user is allowed here
        if (!isset($this->config->item('hub_landing_page')[$dataset['role']])) {
            //PCI requirement: weve removed the callback_user_email_exist() validation
            //dont flag an error message, instead, say email has been sent
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => $this->input->post('login_email'), "partner" => $this->input->post('login_partner')]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
            redirect(current_url());
        }


        $this->process_reset_password_request($dataset, $this->input->post('login_partner'));
    }

    protected function process_reset_password_request($user_data, $partner_id = null)
    {
        //generate and update new password
        $new_password = random_string('alnum', $this->config->item('mm8_system_password_length'));
        $new_password_crypted = better_crypt($new_password);
        if ($this->users_model->set_user_profile(['password' => $new_password_crypted], $user_data['id']) === false) {
            $this->session->set_flashdata('error_message', ERROR_400);
            redirect(current_url());
        }

        //generate url, prepare tokens
        $token1 = $this->encryption->url_encrypt($user_data['id']);
        $token2 = $this->encryption->url_encrypt($new_password);
        if (!$token1 || !$token2) {
            $this->session->set_flashdata('error_message', ERROR_400);
            redirect(current_url());
        }

        $url_link = base_url() . "login/reset/1/" . $token1 . "/" . $token2;

        $template = $this->communications_model->get_email_template('hub_reset_password');
        if (!$template) {
            $this->session->set_flashdata('error_message', ERROR_400);
            redirect(current_url());
        }

        $search_for = ["[NAME]", "[LINK]"];
        $replace_with = [$user_data['first_name'], $url_link];
        $html_template = str_replace($search_for, $replace_with, $template['html_template']);

        //send email!
        $email_result = $this->email_library->send_mpa_email_styled("", "", $user_data['id'], "", "", "", $user_data['email'], "", "", $template['subject'], $html_template);
        if ($email_result['status'] == STATUS_OK) {
            $this->users_model->log_audit_trail(null, "password_reset_request", json_encode(["id" => $user_data['id'], "email" => $user_data['email'], "partner" => $partner_id]));
            $this->session->set_flashdata('success_message', 'An email has been sent to the supplied email address. Follow the instructions in the email to continue.');
        } else {
            $this->session->set_flashdata('error_message', ERROR_406);
        }

        redirect(current_url());
    }

    /**
     *
     * Reset Password
     * Link given to user after a forgot password request (login)
     *
     */
    public function reset($step, $token1, $token2, $token3 = "")
    {

        //validate first
        $agent_id = $this->encryption->url_decrypt($token1);
        $password = $this->encryption->url_decrypt($token2);
        if (!$agent_id || !$password) {
            $this->users_model->log_audit_trail(null, "password_reset_failed", null);
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $dataset = $this->users_model->get_user_profile($agent_id);
        //at this stage account should already be confirmed = 1 and verified = 1
        if ($dataset === false || count($dataset) <= 0 || (int) $dataset['active'] == STATUS_NG || (int) $dataset['confirmed'] == STATUS_NG || (int) $dataset['verified'] == STATUS_NG || crypt($password, $dataset['password']) != $dataset['password']) {
            $this->users_model->log_audit_trail(null, "password_reset_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }


        $method = 'reset_step_' . $step;
        if (!method_exists($this, $method)) {
            $this->users_model->log_audit_trail(null, "password_reset_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $this->{$method}($dataset, $token1, $token2, $token3);
        $this->session->sess_destroy();
    }

    protected function reset_step_1($dataset, $token1, $token2, $token3)
    {
        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "password_reset";
        $view_data['action_url_prefix'] = base_url() . "login/reset/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Reset Password";
        $view_data['description_html'] = "<p>Before you can reset your password, you need to verify your identity with a security code.</p><p>How would you like to receive your code?</p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step1', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            self::process_step_1($view_data);
        }
    }

    protected function reset_step_2($dataset, $token1, $token2, $token3)
    {
        //validate: at this point verification_code should have been set
        //if not, it means the user didnt come from step1
        if (empty($dataset['verification_code'])) {
            $this->users_model->log_audit_trail(null, "password_reset_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            return;
        }


        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "password_reset";
        $view_data['action_url_prefix'] = base_url() . "login/reset/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Reset Password";
        $view_data['description_html'] = "<p>A verification code has been sent to you. Please enter your code below to continue.</p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step2', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            self::process_step_2($view_data);
        }
    }

    protected function reset_step_3($dataset, $token1, $token2, $token3)
    {
        //validate: at this point $token3 should not be empty and should be same as whats stored in db
        $verification_code = $this->encryption->url_decrypt($token3);
        if (!$verification_code || empty($dataset['verification_code']) || $verification_code != $dataset['verification_code']) {
            $this->users_model->log_audit_trail(null, "password_reset_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            return;
        }

        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "password_reset";
        $view_data['action_url_prefix'] = base_url() . "login/reset/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Reset Password";
        $view_data['description_html'] = "<p>Please enter a new password. Once updated, you will be redirected to the login page and continue using your new password.</p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";
        $view_data['show_password_meter'] = true;

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step3', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            $view_data['redirect_url'] = base_url();
            $view_data['redirect_url_method'] = "refresh";
            self::process_step_3($view_data);
        }
    }

    /**
     *
     * Verify Account
     * Link given to user after a new user has been created
     *
     */
    public function verify_account($step, $token1, $token2, $token3 = "")
    {

        //validate first
        $agent_id = $this->encryption->url_decrypt($token1);
        $password = $this->encryption->url_decrypt($token2);
        if (!$agent_id || !$password) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", null);
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $dataset = $this->users_model->get_user_profile($agent_id);
        //at this stage account should still be confirmed = 0 and verified = 0
        if ($dataset === false || count($dataset) <= 0 || (int) $dataset['active'] == STATUS_NG || (int) $dataset['confirmed'] == STATUS_OK || (int) $dataset['verified'] == STATUS_OK || crypt($password, $dataset['password']) != $dataset['password']) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            log_message('error', __FILE__ . ':' . __LINE__ . ' dataset=' . json_encode($dataset));
            return;
        }


        $method = 'verify_step_' . $step;
        if (!method_exists($this, $method)) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            log_message('error', __FILE__ . ':' . __LINE__ . ' method=' . $method);
            return;
        }

        $this->{$method}($dataset, $token1, $token2, $token3);
        $this->session->sess_destroy();
    }

    protected function verify_step_1($dataset, $token1, $token2, $token3)
    {
        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "verify_account";
        $view_data['action_url_prefix'] = base_url() . "login/verify-account/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Welcome to " . $this->config->item('mm8_system_name') . "!";
        $view_data['description_html'] = "<p>Congratulations! You're one step closer to gaining access to the " . $this->config->item('mm8_product_name') . ". But first, you need to update your password.</p><p>Verify your identity with a security code. How would you like to receive your code?</p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step1', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            self::process_step_1($view_data);
        }
    }

    protected function verify_step_2($dataset, $token1, $token2, $token3)
    {
        //validate: at this point verification_code should have been set
        //if not, it means the user didnt come from step1
        if (empty($dataset['verification_code'])) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            return;
        }


        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "verify_account";
        $view_data['action_url_prefix'] = base_url() . "login/verify-account/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Welcome to " . $this->config->item('mm8_system_name') . "!";
        $view_data['description_html'] = "<p>A verification code has been sent to you. Please enter your code below to continue.</p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step2', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            self::process_step_2($view_data);
        }
    }

    protected function verify_step_3($dataset, $token1, $token2, $token3)
    {
        //validate: at this point $token3 should not be empty and should be same as whats stored in db
        $verification_code = $this->encryption->url_decrypt($token3);
        if (!$verification_code || empty($dataset['verification_code']) || $verification_code != $dataset['verification_code']) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));
            $this->load->view('errors/link_expired');
            return;
        }

        $view_data = [];
        $view_data['user_data'] = $dataset;
        $view_data['action'] = "verify_account";
        $view_data['action_url_prefix'] = base_url() . "login/verify-account/";
        $view_data['token1'] = $token1;
        $view_data['token2'] = $token2;
        $view_data['token3'] = $token3;
        $view_data['hero_html'] = "Welcome to " . $this->config->item('mm8_system_name') . "!";
        $view_data['description_html'] = "<p>Please enter a new password. </p>";
        $view_data['background_image'] = asset_url() . "/img/37.png";
        $view_data['show_password_meter'] = true;

        if (count($this->input->post()) <= 0) {
            $this->load->view('login/generic_step3', $view_data);
        } else {
            //ACTION
            $view_data['post_data'] = $this->input->post();
            $view_data['redirect_url'] = base_url() . "login/login-account-verified/" . $token1 . "/" . $this->encryption->url_encrypt($view_data['post_data']['login_password']);
            $view_data['redirect_url_method'] = "auto";
            self::process_step_3($view_data);
        }
    }

    /**
     *
     * Helper functions for PASSWORD RESET and VERIFY ACCOUNT
     *
     */
    protected function process_step_1($dataset)
    {
        //generate and update new reset code
        $new_code = strtoupper(random_string('numeric', 6));

        if (!$this->users_model->set_user_profile(['verification_code' => $new_code], $dataset['user_data']['id'])) {
            $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
            $this->session->set_flashdata('error_message', ERROR_400);
            redirect(current_url());
        }


        //EMAIL (1)
        if ($dataset['post_data']['verifyCodeMethod'] == "1" && !empty($dataset['user_data']['email'])) {
            $template = $this->communications_model->get_email_template('hub_verification_code');
            if (!$template) {
                $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
                $this->session->set_flashdata('error_message', ERROR_400);
                redirect(current_url());
            }

            $search_for = ["[NAME]", "[VERIFICATIONCODE]"];
            $replace_with = [$dataset['user_data']['first_name'], $new_code];
            $html_template = str_replace($search_for, $replace_with, $template['html_template']);

            //send email!
            $email_result = $this->email_library->send_mpa_email_styled("", "", $dataset['user_data']['id'], "", "", "", $dataset['user_data']['email'], "", "", $template['subject'], $html_template);
            if ($email_result['status'] != STATUS_OK) {
                $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
                $this->session->set_flashdata('error_message', ERROR_406);
                redirect(current_url());
            }
        }
        //SMS (2)
        elseif ($dataset['post_data']['verifyCodeMethod'] == "2" && !empty($dataset['user_data']['mobile_phone'])) {
            $template = $this->communications_model->get_sms_template('hub_verification_code');
            if (!$template) {
                $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
                $this->session->set_flashdata('error_message', ERROR_400);
                redirect(current_url());
            }

            $search_for = ["[NAME]", "[VERIFICATIONCODE]"];
            $replace_with = [$dataset['user_data']['first_name'], $new_code];
            $message = str_replace($search_for, $replace_with, $template);

            $sms_result = $this->sms_library->send($this->config->item('mm8_sms_sender'), $dataset['user_data']['mobile_phone'], $message);
            if ($sms_result['status'] != STATUS_OK) {
                $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
                $this->session->set_flashdata('error_message', ERROR_406);
                redirect(current_url());
            }
        } else {
            $this->users_model->log_audit_trail(null, $dataset['action'] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
            $this->load->view('errors/link_expired');
            return;
        }


        //NEXT: STEP 2
        //lets keep changing the tokens
        $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($dataset['token1']));
        $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($dataset['token2']));
        redirect($dataset['action_url_prefix'] . '2/' . $new_token1 . '/' . $new_token2);
    }

    protected function process_step_2($dataset)
    {
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('verification_code', 'Verification Code', 'trim|required|callback_check_verification_code[' . $dataset['user_data']['verification_code'] . ']');

        if ($this->form_validation->run() == false) {
            $this->load->view('login/generic_step2', $dataset);
        } else {
            //NEXT: STEP 3
            $new_token1 = $this->encryption->url_encrypt($this->encryption->url_decrypt($dataset['token1']));
            $new_token2 = $this->encryption->url_encrypt($this->encryption->url_decrypt($dataset['token2']));
            $new_token3 = $this->encryption->url_encrypt($dataset['user_data']['verification_code']);
            redirect($dataset['action_url_prefix'] . '3/' . $new_token1 . '/' . $new_token2 . '/' . $new_token3);
        }
    }

    protected function process_step_3($dataset)
    {
        //ACTION
        $this->form_validation->set_message('matches', 'Passwords do not match');
        $this->form_validation->set_error_delimiters('<label class="help-block text-left">', '</label>');
        $this->form_validation->set_rules('login_password', 'Password', 'trim|required|max_length[128]|callback_check_password_complexity');
        $this->form_validation->set_rules('login_password_confirm', 'Password', 'trim|required|matches[login_password]');

        if ($this->form_validation->run() == false) {
            $this->load->view('login/generic_step3', $dataset);
            return;
        }


        //Go to work! :)
        $new_password_crypted = better_crypt($dataset['post_data']['login_password']);

        //update fields
        $new_data = [];
        $new_data['password'] = $new_password_crypted;
        $new_data['verification_code'] = null;
        $new_data['lock_expiry'] = null;
        $new_data['failed_attempts'] = 0;
        $new_data['last_password_reset'] = $this->database_tz_model->now();

        if ($this->users_model->set_user_profile($new_data, $dataset['user_data']['id'])) {
            $this->users_model->log_audit_trail(null, $dataset["action"] . "_successful", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
            redirect($dataset["redirect_url"], $dataset["redirect_url_method"]);
        } else {
            $this->users_model->log_audit_trail(null, $dataset["action"] . "_failed", json_encode(["id" => $dataset['user_data']['id'], "email" => $dataset['user_data']['email']]));
            redirect(current_url());
        }
    }

    /**
     *
     * Login - New user account verified
     *
     */
    public function login_account_verified($token1, $token2)
    {
        //validate first
        $agent_id = $this->encryption->url_decrypt($token1);
        $password = $this->encryption->url_decrypt($token2);
        if (!$agent_id || !$password) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", null);
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $dataset = $this->users_model->get_user_profile($agent_id);
        if ($dataset === false || count($dataset) <= 0 || (int) $dataset['active'] == STATUS_NG || crypt($password, $dataset['password']) != $dataset['password']) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }



        //update user fields
        $new_data = [];
        $new_data['verified'] = STATUS_OK;
        $new_data['confirmed'] = STATUS_OK;
        $new_data['logged_in_first_time'] = STATUS_OK;
        $new_data['date_confirmed'] = $new_data['date_logged_in_first_time'] = $this->database_tz_model->now();

        if (!$this->users_model->set_user_profile($new_data, $agent_id)) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }



        //succesful
        $this->initialise_login($dataset);
        $this->users_model->log_audit_trail($dataset['id'], "verify_account_successful", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));

        //REDIRECTING NOW...
        if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
            redirect(base_url() . 'login/account-suspended', 'refresh');
        }
        //redirect to default page based on role
        elseif (isset($this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role])) {

            //add a session variable to notify its user's first time logging in
            $this->session->utilihub_hub_user_first_time = true;

            $this->session->utilihub_hub_landing_page = base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role];
            redirect($this->session->utilihub_hub_landing_page, 'refresh');
        } else {
            $this->session->sess_destroy();
            redirect(base_url(), 'refresh');
        }
    }

    /**
     *
     * Login - Register account verified
     * From Register()
     *
     */
    public function register_account_verified($token1, $token2, $token3 = null)
    {
        //validate first
        $agent_id = $this->encryption->url_decrypt($token1);
        $password = $this->encryption->url_decrypt($token2);
        $agentIs = $this->encryption->url_decrypt($token3);
        if (!$agent_id || !$password) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", null);
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        $dataset = $this->users_model->get_user_profile($agent_id);
        if ($dataset === false || count($dataset) <= 0 || (int) $dataset['active'] == STATUS_NG || (int) $dataset['confirmed'] == STATUS_NG || (int) $dataset['verified'] == STATUS_NG || crypt($password, $dataset['password']) != $dataset['password']) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }


        //update user fields
        //at this stage account should already be confirmed and verified
        //confirmed = 1 and verified is set in Register() before redirecting here
        $new_data = [];
        $new_data['logged_in_first_time'] = STATUS_OK;
        $new_data['date_logged_in_first_time'] = $this->database_tz_model->now();

        if (!$this->users_model->set_user_profile($new_data, $agent_id)) {
            $this->users_model->log_audit_trail(null, "verify_account_failed", json_encode(["id" => isset($dataset['id']) ? $dataset['id'] : null, "email" => isset($dataset['email']) ? $dataset['email'] : null]));
            $this->load->view('errors/link_expired');
            $this->session->sess_destroy();
            return;
        }

        if (!empty($agentIs)) {
            $accountManager = $this->account_manager_model->getById($agentIs);
            if ($accountManager) {
                $dataset['account_manager_id'] = $accountManager->id;
                $agentIS = $this->users_model->get_user_profile($accountManager->account_manager_agent);
                if ($agentIS) {
                    $dataset['account_manager_user_id'] = $agentIS['id'];
                }
            }
        }

        //succesful
        $this->initialise_login($dataset);
        $this->users_model->log_audit_trail($dataset['id'], "verify_account_successful", json_encode(["id" => $dataset['id'], "email" => $dataset['email']]));

        //REDIRECTING NOW...
        if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
            redirect(base_url() . 'login/account-suspended', 'refresh');
        }
        //redirect to default page based on role
        elseif (isset($this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role])) {
            //add a session variable to notify its user's first time logging in
            $this->session->utilihub_hub_user_first_time = true;

            $this->session->utilihub_hub_landing_page = base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_user_role];

            //were adding a param for use with ga/gtm tracking
            $param = '?verified=1';
            redirect($this->session->utilihub_hub_landing_page . $param, 'refresh');
        } else {
            $this->session->sess_destroy();
            redirect(base_url(), 'refresh');
        }
    }

    /**
     *
     * Login Main
     *
     */
    public function account_suspended()
    {
        $this->session->sess_destroy();
        $this->load->view('login/account_suspended');
    }

    /**
     *
     * Utils - Form validation Callbacks()
     *
     */
    public function user_active($email, $active)
    {
        if ((int) $active == STATUS_OK) {
            return true;
        } else {
            $this->form_validation->set_message('user_active', 'Invalid username or password');
            return false;
        }
    }

    public function user_email_exist($email)
    {
        $login_data = $this->users_model->get_user_login($email);
        if (!$login_data) {
            $this->form_validation->set_message('user_email_exist', 'Invalid username or password');
            return false;
        } else {
            return true;
        }
    }

    public function check_database($password_entered, $user_id)
    {
        $login_data = $this->users_model->get_user_profile($user_id);
        if ($login_data === false || count($login_data) <= 0) {
            $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => null]));
            $this->form_validation->set_message('check_database', 'Invalid username or password');
            return false;
        }

        //1. check if user is active, confirmed and verified
        if ((int) $login_data['active'] === STATUS_NG || (int) $login_data['confirmed'] === STATUS_NG || (int) $login_data['verified'] === STATUS_NG) {
            $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => null, "email" => $login_data['email']]));
            $this->users_model->set_failed_login_attempts($login_data['id']);
            $this->form_validation->set_message('check_database', 'Invalid username or password');
            return false;
        }


        //2. check if user is locked
        $locked_mins = $this->users_model->user_minutes_locked($login_data['id']);
        if ($locked_mins != null) {
            if ((int) $locked_mins > 0) {
                $this->users_model->log_audit_trail(null, "login_attempt_locked", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));
                $this->form_validation->set_message('check_database', 'Account locked for ' . $locked_mins . ' minutes due to too many failed login attempts');
                return false;
            } else {
                //reset lockout
                $this->users_model->unlock_user($login_data['id']);
            }
        }

        //3. check if number of failed login attempts has reached threshold
        $failed_attempts = $this->users_model->get_failed_login_attempts($login_data['id']);
        if ($failed_attempts >= $this->config->item('mm8_system_login_lockout_attempts_threshold')) {
            //lock account first
            $this->users_model->lock_user($login_data['id'], $this->config->item('mm8_system_login_lockout_period_in_mins'));

            $this->users_model->log_audit_trail(null, "login_attempt_locked", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));
            $this->form_validation->set_message('check_database', 'Account locked for ' . $this->config->item('mm8_system_login_lockout_period_in_mins') . ' minutes due to too many failed login attempts');
            return false;
        }

        //4. check password is correct
        if (crypt($password_entered, $login_data['password']) != $login_data['password']) {
            $this->users_model->log_audit_trail(null, "login_failed", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));
            $this->users_model->set_failed_login_attempts($login_data['id']);
            $this->form_validation->set_message('check_database', 'Invalid username or password');
            return false;
        }

        //5. check if password has expired
        // at this point we know that the password is correct
        if ((int) $this->config->item('mm8_system_expire_password') == STATUS_OK) {
            $days_since_reset = $this->users_model->days_since_password_reset($login_data['id']);
            if ($days_since_reset == null || (int) $days_since_reset >= $this->config->item('mm8_system_expire_password_after_days')) {
                $this->users_model->log_audit_trail(null, "login_attempt_expired", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));
                $this->form_validation->set_message('check_database', 'Your password has expired and must be changed. To reset your password, <a href = "' . base_url() . 'login/request-reset">click here</a>.');
                return false;
            }
        }

        //succesful
        $this->initialise_login($login_data);
        $this->users_model->log_audit_trail($login_data['id'], "login_successful", json_encode(["method" => USER_LOGIN_BASICAUTH, "id" => $login_data['id'], "email" => $login_data['email']]));

        return true;
    }

    public function check_verification_code($left, $right)
    {
        if ($left != $right) {
            $this->form_validation->set_message('check_verification_code', 'Invalid verification code');
            return false;
        }

        return true;
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

    /**
     *
     * Set session variables before redirecting to pages
     *
     */
    protected function initialise_login($dataset)
    {
        $this->session->utilihub_hub_session = session_id();
        $this->session->utilihub_hub_user_id = $dataset['id'];
        $this->session->utilihub_hub_user_code = $dataset['u_code'];
        $this->session->utilihub_hub_user_role = $dataset['role'];
        $this->session->utilihub_hub_user_verified = $dataset['verified'];
        $this->session->utilihub_hub_user_profile_first_name = $dataset['first_name'];
        $this->session->utilihub_hub_user_profile_last_name = $dataset['last_name'];
        $this->session->utilihub_hub_user_profile_fullname = $dataset['full_name'];
        $this->session->utilihub_hub_user_profile_initials = strtoupper(substr($dataset['first_name'], 0, 1) . substr($dataset['last_name'], 0, 1));
        $this->session->utilihub_hub_user_profile_email = $dataset['email'];
        $this->session->utilihub_hub_user_profile_photo = isset($dataset['profile_photo']) && !empty($dataset['profile_photo']) ? $dataset['profile_photo'] : asset_url() . "img/default/profile-photo.jpg";

        //default
        $this->session->utilihub_hub_user_company_logo = asset_url() . "img/default/partner-logo.png";

        $this->session->utilihub_hub_target_role = $this->session->utilihub_hub_user_role;

        $this->session->utilihub_hub_active_agent_id = null;

        //global UI options
        $global_options = [];
        $global_options['main_menu_collapsed'] = ""; // mini-navbar // ticket 777735942 by default side menus open not closed
        $global_options['micro_menu_display'] = "none";
        $this->session->utilihub_hub_global_view_options = $global_options;

        //user settings
        $this->session->utilihub_hub_user_settings = isset($dataset['hub_user_settings']) && !empty($dataset['hub_user_settings']) ? json_decode($dataset['hub_user_settings'], true) : [];

        //timezone
        if (isset($dataset['timezone']) && !empty($dataset['timezone'])) {
            $tz = new DateTimeZone($dataset['timezone']);
            $offset = $tz->getOffset(new DateTime);
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate('H:i', abs($offset));
            $this->session->utilihub_system_timezone_offset = $offset_prefix . $offset_formatted;
        }

        $data = [
            'amazon_connect_is_logged_in' => STATUS_NG,
        ];
        $this->users_model->set_user_profile($data, $dataset['id']);

        $this->session->utilihub_user_amazon_connect_ccp_init = false;

        //FINAL STEP IN INITIALISE
        $this->users_model->unlock_user($dataset['id']);
        $this->users_model->set_last_login($dataset['id']);
    }

    public function ajax_set_hub_user_settings()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(true);
            return;
        }


        $tmp_data = $this->session->utilihub_hub_user_settings;
        $tmp_data[$this->input->post('key')] = $this->input->post('val');
        $this->session->utilihub_hub_user_settings = $tmp_data;

        //update db
        $this->users_model->set_user_profile(['hub_user_settings' => json_encode($this->session->utilihub_hub_user_settings)], $this->session->utilihub_hub_user_id);

        echo json_encode(true);
        return;
    }

    public function ajax_set_global_display_options()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(false);
            return;
        }

        $dt_params = $this->input->post();

        $global_options = $this->session->utilihub_hub_global_view_options;
        $global_options[$this->input->post('key')] = $this->input->post('val');
        $this->session->utilihub_hub_global_view_options = $global_options;

        // Overview defaults
        $user_data = $this->users_model->get_user_profile($this->session->utilihub_hub_user_id);
        if ($user_data) {
            $overviewDefaults = json_decode($user_data['overview_defaults'], true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $overviewDefaults = [];
            }

            $overviewDefaults['global_display'] = $dt_params;

            $updateOverviewDefaults = [
                'overview_defaults' => json_encode($overviewDefaults),
            ];

            $this->users_model->set_user_profile($updateOverviewDefaults, $this->session->utilihub_hub_user_id);
        }

        echo json_encode(true);
        return;
    }

    /**
     *
     * Load contents of micro menu
     *
     */
    public function ajax_update_account_selector_target_div()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['html' => '']);
            return;
        }

        echo json_encode(['html' => $this->load->view('section_select_account_target_view', ['new_role' => $this->input->post('opt1')], true)]);
    }

    public function ajax_update_target_account()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['redirect_url' => base_url()]);
            return;
        }

        //update roles and ids
        $this->session->utilihub_hub_target_role = $this->input->post('opt1');
        $this->session->utilihub_hub_target_id = $this->input->post('opt2');

        //update active partner
        $this->session->utilihub_hub_active_partner_id = null;
        $this->session->utilihub_hub_active_agent_id = null;
        switch ($this->session->utilihub_hub_target_role) {
            case USER_ADMIN:
                break;
            default:
                break;
        }


        echo json_encode(['redirect_url' => base_url() . $this->config->item('hub_landing_page')[$this->session->utilihub_hub_target_role]]);
    }

    public function ajax_go_home()
    {
        header('Content-Type: application/json;');

        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['redirect_url' => base_url()]);
            return;
        }

        $this->reset_session_vars_before_going_home();

        //REDIRECTING WHERE...
        if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
            echo json_encode(['redirect_url' => base_url() . 'login/account-suspended']);
        } else {
            echo json_encode(['redirect_url' => $this->session->utilihub_hub_landing_page]);
        }
    }

    public function home()
    {
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['redirect_url' => base_url() . 'login']);
            return;
        }

        $this->reset_session_vars_before_going_home();

        //REDIRECTING WHERE...
        if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
            redirect(base_url() . 'login/account-suspended', 'refresh');
        } else {
            redirect($this->session->utilihub_hub_landing_page, 'refresh');
        }
    }

    public function touch_base()
    {
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['redirect_url' => base_url() . 'login']);
            return;
        }

        $dt_params = $this->input->get();

        $this->reset_session_vars_before_going_home();

        //REDIRECTING WHERE...
        if ($this->session->utilihub_hub_stripe_customer_account_suspended) {
            redirect(base_url() . 'login/account-suspended', 'refresh');
        }
        //but were not going home! were going back to caller
        elseif (isset($dt_params['caller']) && !empty($dt_params['caller'])) {
            $url = $this->encryption->url_decrypt($dt_params['caller']);
            if ($url) {
                redirect($url, 'refresh');
            }
        }
        //catch if above fails!
        else {
            redirect($this->session->utilihub_hub_landing_page, 'refresh');
        }
    }

    protected function reset_session_vars_before_going_home()
    {
        $this->session->utilihub_hub_target_role = $this->session->utilihub_hub_user_role;

        //update active partner
        $this->session->utilihub_hub_active_partner_id = null;
        $this->session->utilihub_hub_active_agent_id = null;
        switch ($this->session->utilihub_hub_target_role) {
            case USER_ADMIN:
                $this->load->model('partner_model');
                $partner_data = $this->partner_model->get_partner_info($this->session->utilihub_hub_target_id);
                if (count($partner_data) > 0) {
                    $this->session->utilihub_hub_active_partner_id = $this->session->utilihub_hub_target_id;
                    $this->session->utilihub_hub_active_agent_id = $partner_data['super_agent'];
                }
                break;
            default:
                break;
        }
    }

    /*
     *
     * Use this endpoint if you want user to redirect on a given URL and its not loggedin
     * We cannot insert redirect parameter to login i.e. login?redirect=https://domain.com due to security inspection
     *
     */

    public function init($token = null)
    {
        if (empty($token)) {
            $this->session->sess_destroy();
            redirect(base_url(), 'refresh');
        }

        $redirect = $this->encryption->url_decrypt($token);
        if (filter_var($redirect, FILTER_VALIDATE_URL)) {
            if (strpos($redirect, $this->config->item('base_url')) !== false) {
                if (!$this->session->utilihub_hub_session) {
                    if (isset($this->session->utilihub_hub_redirect)) {
                        unset($this->session->utilihub_hub_redirect);
                    }
                    $this->session->utilihub_hub_redirect = $redirect;
                    redirect('login', 'refresh');
                } else {
                    redirect($redirect, 'refresh');
                }
            }
        }

        redirect('login', 'refresh');
    }

    /*
     *
     * $this->session->sess_destroy() destroy as well session flashdata hence error message flashdata wont display. Better destroy manually
     *
     */

    private function _sessionDestroy()
    {
        $user_data = $this->session->userdata();
        foreach ($user_data as $key => $value) {
            if ($key != '__ci_last_regenerate' && $key != '__ci_vars') {
                $this->session->unset_userdata($key);
            }
        }
    }

    private function verify_agent($agent_id)
    {
        $new_data = [];
        $new_data['verified'] = STATUS_OK;
        $new_data['confirmed'] = STATUS_OK;
        $new_data['logged_in_first_time'] = STATUS_OK;
        $new_data['date_confirmed'] = $new_data['date_logged_in_first_time'] = $this->database_tz_model->now();

        $this->users_model->set_user_profile($new_data, $agent_id);
    }

    protected function notify_owner_amsis_have_verified($agent_id, $agent_email, $agent_first_name)
    {

        //generate and update new password
        $new_password = random_string('alnum', $this->config->item('mm8_system_password_length'));
        $new_password_crypted = better_crypt($new_password);
        $this->users_model->set_user_profile(['password' => $new_password_crypted], $agent_id);

        //generate url, prepare tokens
        $token1 = $this->encryption->url_encrypt($agent_id);
        $token2 = $this->encryption->url_encrypt($new_password);
        if (!$token1 || !$token2) {
            return false;
        }

        $url_link = base_url() . "login/reset/1/" . $token1 . "/" . $token2;

        $template = $this->communications_model->get_email_template('ams_notify_owner_is_have_verified');
        if (!$template) {
            return false;
        }

        $search_for = ["[NAME]", "[LINK]"];
        $replace_with = [$agent_first_name, $url_link];
        $html_template = str_replace($search_for, $replace_with, $template['html_template']);
        $text_template = str_replace($search_for, $replace_with, $template['text_template']);

        //queue email
        $email_dataset = [];
        $email_dataset['category_id'] = EMAIL_SUBSCRIPTION_REPORTS;
        $email_dataset['from'] = $this->config->item('mm8_system_noreply_email');
        $email_dataset['from_name'] = $this->config->item('mm8_system_name');
        $email_dataset['to'] = $agent_email;
        $email_dataset['cc'] = '';
        $email_dataset['bcc'] = $this->config->item('mm8_system_ops_email');
        $email_dataset['subject'] = $template['subject'];
        $email_dataset['html_message'] = $this->load->view('html_email/basic_mail', ['contents' => $html_template], true);
        $email_dataset['text_message'] = $text_template;

        if ($this->communications_model->queue_email($email_dataset) === false) {
            $this->users_model->log_audit_trail(null, "ams_is_verified_account", json_encode(["id" => $agent_id, "email" => $agent_email]));
            return true;
        } else {
            return false;
        }

        return true;
    }

    public function user_email_blacklisted($email)
    {
        $result = is_email_blacklisted($email);
        if (isset($result[$email]) && $result[$email] === true) {
            $this->form_validation->set_message('user_email_blacklisted', ERROR_614);
            return false;
        } else {
            return true;
        }
    }
}
