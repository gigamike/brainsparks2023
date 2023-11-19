<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customers_model extends CI_Model
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

        $this->db->from('tbl_customers');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['u_code']) && !empty($filter['u_code'])) {
                $this->db->where('u_code', $filter['u_code']);
            }

            if (isset($filter['email']) && !empty($filter['email'])) {
                $this->db->where('email', $filter['email']);
            }

            if (isset($filter['mobile_phone']) && !empty($filter['mobile_phone'])) {
                $this->db->where('mobile_phonel', $filter['mobile_phone']);
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
        $this->db->from('tbl_customers');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }

            if (isset($filter['u_code']) && !empty($filter['u_code'])) {
                $this->db->where('u_code', $filter['u_code']);
            }

            if (isset($filter['email']) && !empty($filter['email'])) {
                $this->db->where('email', $filter['email']);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_customers', ['id' => $id]);
        return $query->row();
    }

    public function getByUCode($u_code)
    {
        $query = $this->db->get_where('tbl_customers', ['u_code' => $u_code]);
        return $query->row();
    }

    public function getByEmail($email)
    {
        $query = $this->db->get_where('tbl_customers', ['email' => $email]);
        return $query->row();
    }

    public function getByMobilePhone($mobile_phone)
    {
        $query = $this->db->get_where('tbl_customers', ['mobile_phone' => $mobile_phone]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_customers', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_customers');

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
        return $this->db->delete('tbl_customers');
    }

    public function dt_get_customers($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "u_code" => "tbl_customers.u_code",
            "first_name" => "tbl_customers.first_name",
            "last_name" => "last_name",
            "email" => "email",
            "mobile_phone" => "mobile_phone",
            "date_of_birth" => "date_of_birth",
            "date_added" => "date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                    id
                    , u_code
                    , first_name
                    , last_name
                    , email
                    , mobile_phone
                    , date_of_birth
                    , profile_photo
                    , DATE_FORMAT(date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_customers
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

    public function dt_get_customers_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(id) AS cnt
        FROM tbl_customers
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }



    public function dt_get_wfb_customers_summary($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "u_code" => "tbl_customers.u_code",
            "first_name" => "tbl_customers.first_name",
            "last_name" => "tbl_customers.last_name",
            "email" => "tbl_customers.email",
            "mobile_phone" => "tbl_customers.mobile_phone",
            "age" => "tbl_customers.date_of_birth",
            "date_of_birth" => "tbl_customers.date_of_birth",
            "date_added" => "tbl_customers.date_added",
            "date_modified" => "tbl_customers.date_modified",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                tbl_customers.id AS customer_id
                , tbl_customers.u_code
                , tbl_customers.first_name
                , tbl_customers.last_name
                , tbl_customers.email
                , tbl_customers.mobile_phone
                , DATE_FORMAT(tbl_customers.date_of_birth, '" . $this->config->item('mm8_db_date_format') . "') AS date_of_birth
                , TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, CURDATE()) AS age
                , DATE_FORMAT(tbl_customers.date_added, '" . $this->config->item('mm8_db_date_format') . "') AS date_added
                , DATE_FORMAT(tbl_customers.date_modified, '" . $this->config->item('mm8_db_date_format') . "') AS date_modified
                FROM tbl_customers

                " . $condition . " "
                . $order_by . "
                LIMIT " . $start . "," . $length;
        // echo $qstr; exit();

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_wfb_customers_count($condition = "")
    {
        $qstr = "SELECT
                  COUNT(DISTINCT(tbl_customers.id)) AS cnt
                  FROM tbl_customers

                  " . $condition . "
                  LIMIT 1";

        // echo $qstr;

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }
}
