<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Connect;
use Aws\Exception\AwsException;
use Aws\Connect\Exception\ConnectException;

if (!function_exists('is_amazon_connect')) {
    function is_amazon_connect()
    {
        return true;
    }
}

if (!function_exists('amazon_connect_ccp_render')) {
    function amazon_connect_ccp_render()
    {
        $CI = & get_instance();

        if (is_amazon_connect()) {
            $CI->load->library('encryption');

            $view_data = [];
            $view_data['user_profile'] = $CI->users_model->get_user_profile($CI->session->utilihub_hub_user_id);
            return $CI->load->view('amazon_connect/template_stream', $view_data);
        }
    }
}

if (!function_exists('amazon_connect_describe_queue')) {
    function amazon_connect_describe_queue($queue_id)
    {
        $CI = & get_instance();

        if (!empty($queue_id)) {
            $client = new Aws\Connect\ConnectClient([
                'version' => '2017-08-08',
                'region' => $CI->config->item('mm8_amazon_connect_aws_region'),
                'credentials' => [
                    'key'    => $CI->config->item('mm8_aws_access_key_id'),
                    'secret' => $CI->config->item('mm8_aws_secret_access_key'),
                ],
            ]);

            $result = $client->describeQueue([
                'InstanceId' => $CI->config->item('mm8_amazon_connect_InstanceId'), // REQUIRED
                'QueueId' => $queue_id, // REQUIRED
            ]);

            return $result;
        }

        return false;
    }
}
