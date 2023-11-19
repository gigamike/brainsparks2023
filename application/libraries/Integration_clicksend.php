<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Integration_clicksend
{
    protected $CI;
    private $url;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->url = "https://rest.clicksend.com/v3/sms/send";
    }

    public function send_sms($account, $recipient, $message)
    {
        $to = ENVIRONMENT == "production" ? $recipient : $this->CI->config->item('mm8_sms')['clicksend']['recipient'];
        $sender = isset($account['sender']) ? $account['sender'] : "";

        //return values
        $ret_arr = [];
        $ret_arr['source'] = "clicksend";
        $ret_arr['from'] = $sender;
        $ret_arr['to'] = $to;
        $ret_arr['message'] = trim($message);


        //validate params
        if (!isset($account['username']) || empty($account['username']) || !isset($account['auth_token']) || empty($account['auth_token'])) {
            $ret_arr['server_response'] = null;
            $ret_arr['status'] = STATUS_NG;
            $ret_arr['api_message_id'] = "";
            return $ret_arr;
        }


        //if (ENVIRONMENT == "production") {
        if (ENVIRONMENT != "development") {
            //dataset
            $tmp_data = ['to' => $to, 'body' => trim($message)];
            if (!empty($sender)) {
                $dataset['from'] = $sender;
            }

            $dataset = ['messages' => []];
            array_push($dataset['messages'], $tmp_data);


            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $this->url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataset));
            curl_setopt($curl, CURLOPT_USERPWD, $account['username'] . ":" . $account['auth_token']);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
            $curl_response = curl_exec($curl);
            $curl_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $json_obj = json_decode($curl_response);

            $ret_arr['server_response'] = $curl_response;
            $ret_arr['status'] = $curl_response_code == 200 && isset($json_obj->{'response_code'}) && $json_obj->{'response_code'} == "SUCCESS" ? STATUS_OK : STATUS_NG;
            $ret_arr['api_message_id'] = isset($json_obj->{'data'}->{'messages'}[0]->{'message_id'}) ? $json_obj->{'data'}->{'messages'}[0]->{'message_id'} : "";

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
