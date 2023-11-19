<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
*
* GLOBAL
*
 */

/*
*
* This should always return only 1 active/inactive channel
* If it doesnt have any channel, then default chatbot
*
 */
if (!function_exists('openai_get_chat_channel')) {
    function openai_get_chat_channel(
        $session_id,
        $user_id, 
        $first_name,
        $last_name,
        $email,
        $profile_photo,
        $ip
    ) {
        $CI = & get_instance();
        $CI->load->model('openai_chat_channels_model');

        $filter = [
            'session_id' => $session_id,
            'user_id' => $user_id,
        ];
        $order = [
            'date_added DESC'
        ];
        $chatChannel = $CI->openai_chat_channels_model->fetch($filter, $order, 1);
        if (count($chatChannel) <= 0) {
            $CI->db->trans_begin();

            $data = [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'profile_photo' => $profile_photo,
                'ip' => $ip,
            ];
            $chat_channel_id = $CI->openai_chat_channels_model->save($data);
            if (!$chat_channel_id) {
                $CI->db->trans_rollback();
                return false;
            }

            //COMMIT
            if ($CI->db->trans_status() === false) {
                $CI->db->trans_rollback();
                return false;
            }

            $CI->db->trans_commit();

            return $CI->openai_chat_channels_model->getById($chat_channel_id);
        } else {
            return $chatChannel[0];
        }
    }
}


if (!function_exists('openai_get_new_chats')) {
    function openai_get_new_chats()
    {
        $CI = & get_instance();
        $CI->load->model('openai_chat_messages_model');

        $filter = [
            // 'date_added' => date('Y-m-d', strtotime($CI->database_tz_model->now())),
            'openai_user_id_is_null' => true,
            'date_read_is_null' => true,
        ];
        return $CI->openai_chat_messages_model->getCountChatChannelWithMessages($filter);
    }
}
