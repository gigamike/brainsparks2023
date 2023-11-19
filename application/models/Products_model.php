<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null, $fields = [])
    {
        if (count($fields) > 0) {
            $this->db->select(implode(',', $fields));
        }

        $this->db->from('tbl_products');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['u_code']) && !empty($filter['u_code'])) {
                $this->db->where('u_code', $filter['u_code']);
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

    public function getCount($filter = [])
    {
        $this->db->select('COUNT(*) AS count_id');
        $this->db->from('tbl_products');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['u_code']) && !empty($filter['u_code'])) {
                $this->db->where('u_code', $filter['u_code']);
            }

            if (isset($filter['name']) && !empty($filter['name'])) {
                $this->db->where('name', $filter['name']);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_products', ['id' => $id]);
        return $query->row();
    }

    public function getByUCode($u_code)
    {
        $query = $this->db->get_where('tbl_products', ['u_code' => $u_code]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_products', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_products');

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
        return $this->db->delete('tbl_products');
    }

    public function dt_get_products($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "u_code" => "tbl_products.u_code",
            "name" => "tbl_products.name",
            "price" => "tbl_products.price",
            "date_added" => "date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                    id
                    , u_code
                    , name
                    , photo
                    , price
                    , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_products
        " . $condition . $order_by . "
        LIMIT " . $start . "," . $length;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_products_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(id) AS cnt
        FROM tbl_products
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function getProducts($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
            u_code
            , name
            , description
            , price
            , photo
        ");
        $this->db->from('tbl_products');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            
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
}
