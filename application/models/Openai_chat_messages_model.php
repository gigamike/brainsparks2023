<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Openai_chat_messages_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
            *
            , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_added_formatted
            , TIMESTAMPDIFF(HOUR, date_added, NOW()) AS date_added_hours_ago
          ");
        $this->db->from('tbl_openai_chat_messages');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['chat_channel_id']) && !empty($filter['chat_channel_id'])) {
                $this->db->where('chat_channel_id', $filter['chat_channel_id']);
            }
            if (isset($filter['date_added_is_less_than_seconds']) && !empty($filter['date_added_is_less_than_seconds'])) {
                $this->db->where("TIMESTAMPDIFF(SECOND, date_added, NOW()) <= " . $filter['date_added_is_less_than_seconds'], null, false);
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
        $this->db->select('COUNT(tbl_openai_chat_messages.id) AS count_id');
        $this->db->from('tbl_openai_chat_messages');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['chat_channel_id']) && !empty($filter['chat_channel_id'])) {
                $this->db->where('chat_channel_id', $filter['chat_channel_id']);
            }
            if (isset($filter['date_added_is_less_than_seconds']) && !empty($filter['date_added_is_less_than_seconds'])) {
                $this->db->where("TIMESTAMPDIFF(SECOND, date_added, NOW()) <= " . $filter['date_added_is_less_than_seconds'], null, false);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_openai_chat_messages', ['id' => $id]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_openai_chat_messages', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_openai_chat_messages');

        return $data['id'];
    }

    public function updateByIds($data, $ids)
    {
        $this->db->where_in('id', $ids);
        $this->db->update('tbl_openai_chat_messages', $data);
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
        return $this->db->delete('tbl_openai_chat_messages');
    }

    public function getCountChatChannelWithMessages($filter = [])
    {
        $this->db->select('COUNT(DISTINCT(tbl_openai_chat_messages.chat_channel_id)) AS count_id');
        $this->db->from('tbl_openai_chat_messages');
        $this->db->join('tbl_connect_sd_chat_message_read', 'tbl_connect_sd_chat_message_read.chat_message_id = tbl_openai_chat_messages.id', 'left');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['date_added'])) {
                $this->db->where("DATE(tbl_openai_chat_messages.date_added) = '" . $filter['date_added'] . "'");
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }
}
