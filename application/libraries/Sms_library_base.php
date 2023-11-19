<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sms_library_base
{
    protected $CI;
    private $sms_config;
    private $sms_provider;

    public function __construct()
    {
        $this->CI = & get_instance();

        $this->sms_config = $this->CI->config->item('mm8_sms');
        $this->sms_provider = $this->sms_config['provider'];
    }

    public function send($sender, $recipient, $message)
    {
        $recipient = self::convert_e164($recipient);
        
        return $this->{'send_' . $this->sms_provider . '_sms'}($sender, $recipient, $message);
    }

    public function send_template($action, $partner_id, $sender, $recipient, $dataset = [], $append_unsubscribe = true)
    {
        $this->CI->load->model('communications_model', '', true);
        $message_body = $this->CI->communications_model->get_sms_template($action, $partner_id);

        if ($message_body == "") {
            return false;
        }

        if (count($dataset) > 0) {
            $search_for = [];
            $replace_with = [];
            foreach ($dataset as $key => $value) {
                array_push($search_for, $key);
                array_push($replace_with, $value);
            }

            $message_body = str_replace($search_for, $replace_with, $message_body);
        }

        if($append_unsubscribe && $this->CI->config->item('mm8_system_feature_sms_unsubscribe') == STATUS_OK)
            $message_body .= '. To unsubscribe please reply STOP.';

        return self::send($sender, $recipient, $message_body);
    }

    public function send_by_provider($provider, $recipient, $message, $append_unsubscribe = true)
    {
        $recipient = self::convert_e164($recipient);

        if($append_unsubscribe && $this->CI->config->item('mm8_system_feature_sms_unsubscribe') == STATUS_OK)
            $message .= '. To unsubscribe please reply STOP.';

        switch ($provider) {
            case "twilio":
                $account_data = $this->CI->partner_model->get_partner_twilio_account();
                if (empty($account_data)) {
                    return false;
                }

                $params = [];
                $params['account_sid'] = isset($account_data['account_sid']) && !empty($account_data['account_sid']) ? $this->CI->encryption->decrypt($account_data['account_sid']) : "";
                $params['auth_token'] = isset($account_data['auth_token']) && !empty($account_data['auth_token']) ? $this->CI->encryption->decrypt($account_data['auth_token']) : "";
                $params['sender'] = isset($account_data['sender']) ? $account_data['sender'] : "";
                $this->CI->load->library('integration_twilio');
                $response = $this->CI->integration_twilio->send_sms($params, $recipient, $message);
                break;
            case "clickatell":
                $account_data = $this->CI->partner_model->get_partner_clickatell_account();
                if (empty($account_data)) {
                    return false;
                }

                $params = [];
                $params['auth_token'] = isset($account_data['auth_token']) && !empty($account_data['auth_token']) ? $this->CI->encryption->decrypt($account_data['auth_token']) : "";
                $params['sender'] = isset($account_data['sender']) ? $account_data['sender'] : "";
                $this->CI->load->library('integration_clickatell');
                $response = $this->CI->integration_clickatell->send_sms($params, $recipient, $message);
                break;
            case "clicksend":
                $account_data = $this->CI->partner_model->get_partner_clicksend_account();
                if (empty($account_data)) {
                    return false;
                }

                $params = [];
                $params['username'] = isset($account_data['username']) && !empty($account_data['username']) ? $this->CI->encryption->decrypt($account_data['username']) : "";
                $params['auth_token'] = isset($account_data['auth_token']) && !empty($account_data['auth_token']) ? $this->CI->encryption->decrypt($account_data['auth_token']) : "";
                $params['sender'] = isset($account_data['sender']) ? $account_data['sender'] : "";
                $this->CI->load->library('integration_clicksend');
                $response = $this->CI->integration_clicksend->send_sms($params, $recipient, $message);
                break;
            case "default":
                $partner_data = $this->CI->partner_model->get_partner_info();
                if (empty($partner_data)) {
                    return false;
                }

                $sender = !empty($partner_data['sms_sender']) ? $partner_data['sms_sender'] : (isset($this->CI->config->item('mm8_sms')[$this->sms_provider]['sender']) && !empty($this->CI->config->item('mm8_sms')[$this->sms_provider]['sender']) ? $this->CI->config->item('mm8_sms')[$this->sms_provider]['sender'] : '');
                $response = self::send($sender, $recipient, $message);
                break;
            default:
                return false;
        }

        return $response;
    }

    public function ams_send_by_provider($provider, $recipient, $message)
    {
        $recipient = self::convert_e164($recipient);

        switch ($provider) {
            case "twilio":
                $params = [
                    'account_sid' => $this->CI->config->item('mm8_ams_sms')['twilio']['account_sid'],
                    'auth_token' => $this->CI->config->item('mm8_ams_sms')['twilio']['auth_token'],
                    'sender' => $this->CI->config->item('mm8_ams_sms')['twilio']['sender'],
                ];

                $this->CI->load->library('integration_twilio');
                $response = $this->CI->integration_twilio->send_sms($params, $recipient, $message);

                break;
            case "clickatell":
                $params = [
                    'auth_token' => $this->CI->config->item('mm8_ams_sms')['clickatell']['auth_token_prod'],
                    'sender' => $this->CI->config->item('mm8_ams_sms')['clickatell']['sender'],
                ];

                $this->CI->load->library('integration_clickatell');
                $response = $this->CI->integration_clickatell->send_sms($params, $recipient, $message);

                break;
            case "clicksend":
                $params = [
                    'username' => $this->CI->config->item('mm8_ams_sms')['clicksend']['username'],
                    'auth_token' => $this->CI->config->item('mm8_ams_sms')['clicksend']['auth_token'],
                    'sender' => $this->CI->config->item('mm8_ams_sms')['clicksend']['sender'],
                ];

                $this->CI->load->library('integration_clicksend');
                $response = $this->CI->integration_clicksend->send_sms($params, $recipient, $message);

                break;
            default:
                return false;
        }

        return $response;
    }

    public function send_template_by_provider($provider, $partner_id, $action, $recipient, $dataset = [])
    {
        $this->CI->load->model('communications_model', '', true);
        $message_body = $this->CI->communications_model->get_sms_template($action, $partner_id);

        if (empty($message_body)) {
            return false;
        }


        if (count($dataset) > 0) {
            $search_for = array_keys($dataset);
            $replace_with = array_values($dataset);
            $message_body = str_replace($search_for, $replace_with, $message_body);
        }


        return self::send_by_provider($provider, $partner_id, $recipient, $message_body);
    }

    public function convert_e164($phone_number)
    {
        $phone_number = preg_replace('/\s+/', '', $phone_number);


        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->CI->config->item('mm8_country_code')) {
            case "AU":
                //+61123456789
                //61123456789
                if (preg_match("/^(\+)?61(\d{9})$/", $phone_number, $match)) {
                    return $this->country_code . $match[2];
                }
                //0123456789
                elseif (preg_match("/^0(\d{9})$/", $phone_number, $match)) {
                    return $this->country_code . $match[1];
                }
                break;
            case "NZ":
                if (preg_match("/^(\+)?64(\d{8,10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[2];
                } elseif (preg_match("/^0(\d{8,10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[1];
                }
                break;
            case "UK":
                if (preg_match("/^(\+)?44(\d{10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[2];
                } elseif (preg_match("/^0(\d{10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[1];
                }
                break;
            case "US":
                if (preg_match("/^(\+)?1(\d{10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[2];
                } elseif (preg_match("/^(\d{10})$/", $phone_number, $match)) {
                    return $this->country_code . $match[1];
                }
                break;
            default:
                break;
        }

        return $phone_number;
    }

    //twilio sms sender
    protected function send_twilio_sms($sender, $recipient, $message)
    {
        $params = [];

        $params['account_sid'] = isset($this->sms_config[$this->sms_provider]['account_sid']) && !empty($this->sms_config[$this->sms_provider]['account_sid']) ? $this->sms_config[$this->sms_provider]['account_sid'] : "";
        $params['auth_token'] = isset($this->sms_config[$this->sms_provider]['auth_token']) && !empty($this->sms_config[$this->sms_provider]['auth_token']) ? $this->sms_config[$this->sms_provider]['auth_token'] : "";
        $params['sender'] = (isset($this->sms_config[$this->sms_provider]['sender']) ? $this->sms_config[$this->sms_provider]['sender'] : $sender);

        $this->CI->load->library('integration_twilio');

        return $response = $this->CI->integration_twilio->send_sms($params, $recipient, $message);
    }

    //clickatell sms sender
    protected function send_clickatell_sms($sender, $recipient, $message)
    {
        $params = [];

        if (ENVIRONMENT == "production") {
            $params['auth_token'] = isset($this->sms_config[$this->sms_provider]['auth_token_prod']) && !empty($this->sms_config[$this->sms_provider]['auth_token_prod']) ? $this->sms_config[$this->sms_provider]['auth_token_prod'] : "";
        } else {
            $params['auth_token'] = isset($this->sms_config[$this->sms_provider]['auth_token_dev']) && !empty($this->sms_config[$this->sms_provider]['auth_token_dev']) ? $this->sms_config[$this->sms_provider]['auth_token_dev'] : "";
        }

        $params['sender'] = (isset($this->sms_config[$this->sms_provider]['sender']) ? $this->sms_config[$this->sms_provider]['sender'] : $sender);

        $this->CI->load->library('integration_clickatell');

        return $response = $this->CI->integration_clickatell->send_sms($params, $recipient, $message);
    }

    //clicksend sms sender
    protected function send_clicksend_sms($sender, $recipient, $message)
    {
        $params = [];

        $params['username'] = isset($this->sms_config[$this->sms_provider]['username']) && !empty($this->sms_config[$this->sms_provider]['username']) ? $this->sms_config[$this->sms_provider]['username'] : "";
        $params['auth_token'] = isset($this->sms_config[$this->sms_provider]['auth_token']) && !empty($this->sms_config[$this->sms_provider]['auth_token']) ? $this->sms_config[$this->sms_provider]['auth_token'] : "";
        $params['sender'] = (isset($this->sms_config[$this->sms_provider]['sender']) ? $this->sms_config[$this->sms_provider]['sender'] : $sender);

        $this->CI->load->library('integration_clicksend');

        return $response = $this->CI->integration_clicksend->send_sms($params, $recipient, $message);
    }

    public function convert_e164_to_local($phone_number)
    {
        $phone_number = preg_replace('/\s+/', '', $phone_number);


        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->CI->config->item('mm8_country_code')) {
            case "AU":
                //+61123456789
                //61123456789
                if (preg_match("/^(\+)?61(\d{9})$/", $phone_number, $match)) {
                    return "0" . $match[2];
                }
                //0123456789
                elseif (preg_match("/^0(\d{9})$/", $phone_number, $match)) {
                    return "0" . $match[1];
                }
                // 498883044
                elseif (substr($phone_number, 1)!= 0) {
                    return "0" . $phone_number;
                }
                break;
            case "NZ":
                if (preg_match("/^(\+)?64(\d{8,10})$/", $phone_number, $match)) {
                    return "0" . $match[2];
                } elseif (preg_match("/^0(\d{8,10})$/", $phone_number, $match)) {
                    return "0" . $match[1];
                } elseif (substr($phone_number, 1)!= 0) {
                    return "0" . $phone_number;
                }
                break;
            case "UK":
                if (preg_match("/^(\+)?44(\d{10})$/", $phone_number, $match)) {
                    return "0" . $match[2];
                } elseif (preg_match("/^0(\d{10})$/", $phone_number, $match)) {
                    return "0" . $match[1];
                } elseif (substr($phone_number, 1)!= 0) {
                    return "0" . $phone_number;
                }
                break;
            case "US":
                if (preg_match("/^(\+)?1(\d{10})$/", $phone_number, $match)) {
                    return "0" . $match[2];
                } elseif (preg_match("/^(\d{10})$/", $phone_number, $match)) {
                    return "0" . $match[1];
                } elseif (substr($phone_number, 1)!= 0) {
                    return "0" . $phone_number;
                }
                break;
            default:
                break;
        }

        return $phone_number;
    }

    /*
    *
    * Sample primary phone primary_phone
    * +18502215732
    * +61 401 833 919
    * 0434237703
    *
    *
     */
    public function convert_local_to_e164($phone_number)
    {
        $phone_number = str_replace(' ', '', $phone_number);

        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($this->CI->config->item('mm8_country_code')) {
            case "AU":
                if (substr($phone_number, 0, strlen($this->CI->config->item('mm8_country_calling_code'))) == $this->CI->config->item('mm8_country_calling_code')) {
                    // with + and country code
                    return $phone_number;
                } elseif (substr($phone_number, 0, strlen(str_replace('+', '', $this->CI->config->item('mm8_country_calling_code')))) == str_replace('+', '', $this->CI->config->item('mm8_country_calling_code'))) {
                    // no +
                    return "+" . $phone_number;
                } elseif (substr($phone_number, 0, 1) == 0) {
                    return $this->CI->config->item('mm8_country_calling_code') . substr($phone_number, 1, strlen($phone_number)-0);
                } else {
                    return $this->CI->config->item('mm8_country_calling_code') . $phone_number;
                }
                break;
            case "NZ":
                if (substr($phone_number, 0, strlen($this->CI->config->item('mm8_country_calling_code'))) == $this->CI->config->item('mm8_country_calling_code')) {
                    // with + and country code
                    return $phone_number;
                } elseif (substr($phone_number, 0, strlen(str_replace('+', '', $this->CI->config->item('mm8_country_calling_code')))) == str_replace('+', '', $this->CI->config->item('mm8_country_calling_code'))) {
                    // no +
                    return "+" . $phone_number;
                } elseif (substr($phone_number, 0, 1) == 0) {
                    return $this->CI->config->item('mm8_country_calling_code') . substr($phone_number, 1, strlen($phone_number)-0);
                } else {
                    return $this->CI->config->item('mm8_country_calling_code') . $phone_number;
                }
                break;
            case "UK":
                if (substr($phone_number, 0, strlen($this->CI->config->item('mm8_country_calling_code'))) == $this->CI->config->item('mm8_country_calling_code')) {
                    // with + and country code
                    return $phone_number;
                } elseif (substr($phone_number, 0, strlen(str_replace('+', '', $this->CI->config->item('mm8_country_calling_code')))) == str_replace('+', '', $this->CI->config->item('mm8_country_calling_code'))) {
                    // no +
                    return "+" . $phone_number;
                } elseif (substr($phone_number, 0, 1) == 0) {
                    return $this->CI->config->item('mm8_country_calling_code') . substr($phone_number, 1, strlen($phone_number)-0);
                } else {
                    return $this->CI->config->item('mm8_country_calling_code') . $phone_number;
                }
                break;
            case "US":
                if (substr($phone_number, 0, strlen($this->CI->config->item('mm8_country_calling_code'))) == $this->CI->config->item('mm8_country_calling_code')) {
                    // with + and country code
                    return $phone_number;
                } elseif (substr($phone_number, 0, strlen(str_replace('+', '', $this->CI->config->item('mm8_country_calling_code')))) == str_replace('+', '', $this->CI->config->item('mm8_country_calling_code'))) {
                    // no +
                    return "+" . $phone_number;
                } elseif (substr($phone_number, 0, 1) == 0) {
                    return $this->CI->config->item('mm8_country_calling_code') . substr($phone_number, 1, strlen($phone_number)-0);
                } else {
                    return $this->CI->config->item('mm8_country_calling_code') . $phone_number;
                }
                break;
            default:
                break;
        }

        return $phone_number;
    }
}
