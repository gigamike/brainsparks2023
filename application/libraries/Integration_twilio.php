<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Integration_twilio
{
    protected $CI;
    private $url;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->url = "https://api.twilio.com/2010-04-01/Accounts";
    }

    public function send_sms($account, $recipient, $message)
    {
        $to = ENVIRONMENT == "production" ? $recipient : $this->CI->config->item('mm8_sms')['twilio']['recipient'];
        $sender = isset($account['sender']) ? $account['sender'] : "";

        //return values
        $ret_arr = [];
        $ret_arr['source'] = "twilio";
        $ret_arr['from'] = $sender;
        $ret_arr['to'] = $to;
        $ret_arr['message'] = trim($message);


        //validate params
        if (!isset($account['account_sid']) || empty($account['account_sid']) || !isset($account['auth_token']) || empty($account['auth_token'])) {
            $ret_arr['server_response'] = null;
            $ret_arr['status'] = STATUS_NG;
            $ret_arr['api_message_id'] = "";
            return $ret_arr;
        }


        //if (ENVIRONMENT == "production") {
        if (ENVIRONMENT != "development") {
            //dataset
            $dataset = [];
            $dataset['Body'] = trim($message);
            $dataset['From'] = $sender;
            $dataset['To'] = $to;


            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->url . "/" . $account['account_sid'] . "/Messages.json");
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($dataset));
            curl_setopt($curl, CURLOPT_USERPWD, $account['account_sid'] . ":" . $account['auth_token']);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
            $curl_response = curl_exec($curl);
            curl_close($curl);

            $json_obj = json_decode($curl_response);

            $ret_arr['server_response'] = $curl_response;
            $ret_arr['status'] = isset($json_obj->{'status'}) && in_array($json_obj->{'status'}, ["accepted", "queued", "sending", "sent"]) ? STATUS_OK : STATUS_NG;
            $ret_arr['api_message_id'] = isset($json_obj->{'sid'}) ? $json_obj->{'sid'} : "";

            //sleep a bit
            //1,000,000 (1s) / 100 = 10,000 ms
            usleep(10000);
        } else {
            $ret_arr['server_response'] = null;
            $ret_arr['status'] = STATUS_OK;
            $ret_arr['api_message_id'] = "";
        }

        return $ret_arr;
    }
}
