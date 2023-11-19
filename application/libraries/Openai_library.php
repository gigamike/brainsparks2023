<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Openai_library
{
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();

        $this->CI->load->model('openai_chat_channels_model');
    }

    public function getSessionChatChannel()
    {
        return $this->CI->session->utilihub_user_openai_chat_channel = openai_get_chat_channel(
            $this->CI->session->utilihub_hub_session,
            $this->CI->session->utilihub_hub_user_id,
            $this->CI->session->utilihub_hub_user_profile_first_name,
            $this->CI->session->utilihub_hub_user_profile_last_name,
            $this->CI->session->utilihub_hub_user_profile_email,
            $this->CI->session->utilihub_hub_user_profile_photo,
            get_ip()
        );
    }
}
