<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->from('tbl_settings');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['name']) && !empty($filter['name'])) {
                $this->db->where('name', $filter['name']);
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

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_settings', ['id' => $id]);
        return $query->row();
    }

    public function getByName($name)
    {
        $query = $this->db->get_where('tbl_settings', ['name' => $name]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_settings', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_settings');

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
        return $this->db->delete('tbl_settings');
    }
}
