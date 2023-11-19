<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Openai_chat_channels_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null, $fields = [])
    {
        if (count($fields) > 0) {
            $this->db->select(implode(',', $fields));
        } else {
            $this->db->select("
            *
            , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_added_formatted
            , TIMESTAMPDIFF(HOUR, date_added, NOW()) AS date_added_hours_ago
            ");
        }
        
        $this->db->from('tbl_openai_chat_channels');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['session_id']) && !empty($filter['session_id'])) {
                $this->db->where('session_id', $filter['session_id']);
            }
            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }
            if (isset($filter['reference_code']) && !empty($filter['reference_code'])) {
                $this->db->where('reference_code', $filter['reference_code']);
            }
            if (isset($filter['date_deleted_is_null'])) {
                $this->db->where('date_deleted IS NULL');
            }
        }

        if (!is_null($order) && is_array($order) && count($order) > 0) {
            $this->db->order_by(implode(',', $order));
        }

        if (!is_null($limit) && !is_null($start)) {
            $this->db->limit($limit, $start);
        } elseif (!empty($limit)) {
            $this->db->limit($limit);
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        return $query->result();
    }

    public function getCount($filter = [])
    {
        $this->db->select('COUNT(*) AS count_id');
        $this->db->from('tbl_openai_chat_channels');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['session_id']) && !empty($filter['session_id'])) {
                $this->db->where('session_id', $filter['session_id']);
            }
            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('user_id', $filter['user_id']);
            }
            if (isset($filter['reference_code']) && !empty($filter['reference_code'])) {
                $this->db->where('reference_code', $filter['reference_code']);
            }
            if (isset($filter['date_deleted_is_null'])) {
                $this->db->where('date_deleted IS NULL');
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $this->db->select("
          *
          , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_added_formatted
        ");
        $query = $this->db->get_where('tbl_openai_chat_channels', ['id' => $id]);
        return $query->row();
    }

    public function getByReferenceCode($reference_code)
    {
        $query = $this->db->get_where('tbl_openai_chat_channels', ['reference_code' => $reference_code]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_openai_chat_channels', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_openai_chat_channels');

        return $data['id'];
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tbl_openai_chat_channels');
    }

    public function getChatChannelsWithMessages($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select('
          tbl_openai_chat_channels.id
          , tbl_openai_chat_channels.reference_code
          , tbl_openai_chat_channels.user_id
          , tbl_openai_chat_channels.app
          , tbl_openai_chat_channels.status
          , tbl_openai_chat_channels.first_name
          , tbl_openai_chat_channels.last_name
          , tbl_openai_chat_channels.email
          , tbl_openai_chat_channels.profile_photo
          , (SELECT 
            COUNT(tbl_openai_chat_messages.id) 
            FROM tbl_openai_chat_messages
                LEFT JOIN tbl_openai_chat_message_read ON tbl_openai_chat_message_read.chat_message_id = tbl_openai_chat_messages.id
            WHERE tbl_openai_chat_messages.chat_channel_id = tbl_openai_chat_channels.id
                AND tbl_openai_chat_messages.openai_user_id IS NULL
                AND tbl_openai_chat_message_read.id IS NULL) AS count_messages
            , tbl_openai_users.full_name AS assignee_full_name
        ');
        $this->db->from('tbl_openai_chat_channels');
        $this->db->join('tbl_openai_chat_messages', 'tbl_openai_chat_messages.chat_channel_id =  tbl_openai_chat_channels.id', 'left');
        $this->db->join('tbl_openai_users', 'tbl_openai_users.id = tbl_openai_chat_channels.assignee_openai_user_id', 'left');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['user_id']) && !empty($filter['user_id'])) {
                $this->db->where('tbl_openai_chat_channels.user_id', $filter['user_id']);
            }
            if (isset($filter['app']) && !empty($filter['app'])) {
                $this->db->where('tbl_openai_chat_channels.app', $filter['app']);
            }
            if (isset($filter['app_user_id']) && !empty($filter['app_user_id'])) {
                $this->db->where('tbl_openai_chat_channels.app_user_id', $filter['app_user_id']);
            }
            if (isset($filter['reference_code']) && !empty($filter['reference_code'])) {
                $this->db->where('tbl_openai_chat_channels.reference_code', $filter['reference_code']);
            }
            if (isset($filter['reference_code_not_equal']) && !empty($filter['reference_code_not_equal'])) {
                $this->db->where('tbl_openai_chat_channels.reference_code !=', $filter['reference_code_not_equal']);
            }
            if (isset($filter['email']) && !empty($filter['email'])) {
                $this->db->where('tbl_openai_chat_channels.email', $filter['email']);
            }
            if (isset($filter['first_name']) && !empty($filter['first_name'])) {
                $this->db->where('tbl_openai_chat_channels.first_name', $filter['first_name']);
            }
            if (isset($filter['last_name']) && !empty($filter['last_name'])) {
                $this->db->where('tbl_openai_chat_channels.last_name', $filter['last_name']);
            }
            if (isset($filter['user_ids']) && count($filter['user_ids']) > 0) {
                $this->db->where_in('tbl_openai_chat_channels.user_id', $filter['user_ids']);
            }
            if (isset($filter['user_ids_not_in']) && count($filter['user_ids_not_in']) > 0) {
                $this->db->where_not_in('tbl_openai_chat_channels.user_id', $filter['user_ids_not_in']);
            }
            if (isset($filter['apps']) && count($filter['apps']) > 0) {
                $this->db->where_in('tbl_openai_chat_channels.app', $filter['apps']);
            }
            if (isset($filter['apps_not_in']) && count($filter['apps_not_in']) > 0) {
                $this->db->where_not_in('tbl_openai_chat_channels.app', $filter['apps_not_in']);
            }
            if (isset($filter['statuses']) && count($filter['statuses']) > 0) {
                $this->db->where_in('tbl_openai_chat_channels.status', $filter['statuses']);
            }
            if (isset($filter['statuses_not_in']) && count($filter['statuses_not_in']) > 0) {
                $this->db->where_not_in('tbl_openai_chat_channels.status', $filter['statuses_not_in']);
            }
            if (isset($filter['date_deleted_is_null'])) {
                $this->db->where('tbl_openai_chat_channels.date_deleted IS NULL');
            }
            if (isset($filter['keyword']) && !empty($filter['keyword'])) {
                $this->db->group_start();
                $this->db->or_like('tbl_openai_chat_channels.email', $filter['keyword']);
                $this->db->or_like('tbl_openai_chat_channels.first_name', $filter['keyword']);
                $this->db->or_like('tbl_openai_chat_channels.last_name', $filter['keyword']);
                $this->db->group_end();
            }

            if (isset($filter['reference_code_like']) && !empty($filter['reference_code_like'])) {
                $this->db->like('tbl_openai_chat_channels.reference_code', $filter['reference_code_like']);
            }
            if (isset($filter['reference_code_not_like']) && !empty($filter['reference_code_not_like'])) {
                $this->db->not_like('tbl_openai_chat_channels.reference_code', $filter['reference_code_not_like']);
            }

            if (isset($filter['email_like']) && !empty($filter['email_like'])) {
                $this->db->like('tbl_openai_chat_channels.email', $filter['email_like']);
            }
            if (isset($filter['email_not_like']) && !empty($filter['email_not_like'])) {
                $this->db->not_like('tbl_openai_chat_channels.email', $filter['email_not_like']);
            }

            if (isset($filter['first_name_like']) && !empty($filter['first_name_like'])) {
                $this->db->like('tbl_openai_chat_channels.first_name', $filter['first_name_like']);
            }
            if (isset($filter['first_name_not_like']) && !empty($filter['first_name_not_like'])) {
                $this->db->not_like('tbl_openai_chat_channels.first_name', $filter['first_name_not_like']);
            }

            if (isset($filter['last_name_like']) && !empty($filter['last_name_like'])) {
                $this->db->like('tbl_openai_chat_channels.last_name', $filter['last_name_like']);
            }
            if (isset($filter['last_name_not_like']) && !empty($filter['last_name_not_like'])) {
                $this->db->not_like('tbl_openai_chat_channels.last_name', $filter['last_name_not_like']);
            }
        }

        $this->db->group_by(['tbl_openai_chat_channels.id']);

        if (!is_null($order) && is_array($order) && count($order) > 0) {
            $this->db->order_by(implode(',', $order));
        }

        if (!is_null($limit) && !is_null($start)) {
            $this->db->limit($limit, $start);
        } elseif (!empty($limit)) {
            $this->db->limit($limit);
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        return $query->result();
    }
}
