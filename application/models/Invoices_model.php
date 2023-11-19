<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoices_model extends CI_Model
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

        $this->db->from('tbl_invoices');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['customer_id']) && !empty($filter['customer_id'])) {
                $this->db->where('customer_id', $filter['customer_id']);
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
        $this->db->from('tbl_invoices');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }

            if (isset($filter['customer_id']) && !empty($filter['customer_id'])) {
                $this->db->where('customer_id', $filter['customer_id']);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_invoices', ['id' => $id]);
        return $query->row();
    }

    public function getByUCode($u_code)
    {
        $query = $this->db->get_where('tbl_invoices', ['u_code' => $u_code]);
        return $query->row();
    }

    public function insert($data)
    {
        $this->db->insert('tbl_invoices', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_invoices');

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
        return $this->db->delete('tbl_invoices');
    }

    public function dt_get_invoices($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "invoice_u_code" => "invoice_u_code",
            "date_purchased" => "date_purchased",
            "product_u_code" => "product_u_code",
            "product_name" => "product_name",
            "price" => "price",
            "quantity" => "quantity",
            "date_added" => "date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                    tbl_invoices.id AS invoice_id
                    , tbl_invoices.u_code AS invoice_u_code
                    , DATE_FORMAT(tbl_invoices.date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_purchased
                    , tbl_products.u_code AS product_u_code
                    , tbl_products.name AS product_name
                    , tbl_products.price AS price
                    , tbl_invoice_items.quantity AS quantity
                    , DATE_FORMAT(tbl_invoices.date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_invoices
            INNER JOIN tbl_invoice_items ON tbl_invoice_items.invoice_id = tbl_invoices.id
            INNER JOIN tbl_products ON tbl_products.id = tbl_invoice_items.product_id
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

    public function dt_get_invoices_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(tbl_invoices.id) AS cnt
        FROM tbl_invoices
            INNER JOIN tbl_invoice_items ON tbl_invoice_items.invoice_id = tbl_invoices.id
            INNER JOIN tbl_products ON tbl_products.id = tbl_invoice_items.product_id
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }
}
