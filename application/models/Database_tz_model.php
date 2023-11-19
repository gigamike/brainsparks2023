<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Database_tz_model extends CI_Model
{
    public $iso8601_date_format = "Y-m-d";
    public $iso8601_datetime_format = "Y-m-d\TH:i:s";

    public function __construct()
    {
        parent::__construct();
        $local_tz = $this->session->has_userdata('utilihub_system_timezone_offset') && !empty($this->session->utilihub_system_timezone_offset) ? $this->session->utilihub_system_timezone_offset : $this->config->item('mm8_db_timezone');
        $this->db->simple_query("SET time_zone='" . $local_tz . "'");
    }

    public function now($php_date_format = "")
    {
        $qstr = "SELECT NOW() AS date_now";
        $query = $this->db->query($qstr);

        if (empty($php_date_format)) {
            return ($query && $query->num_rows() > 0) ? $query->row_array()['date_now'] : null;
        } else {
            if ($query && $query->num_rows() > 0) {
                return reformat_str_date($query->row_array()['date_now'], 'Y-m-d H:i:s', $php_date_format);
            } else {
                return null;
            }
        }
    }

    public function interval_days($days, $is_epoch = true, $php_date_format = "")
    {
        if (!is_numeric($days)) {
            return false;
        }

        if ($is_epoch) {
            $qstr = "SELECT UNIX_TIMESTAMP(NOW() + INTERVAL " . $days . " DAY) AS date_time";
            $query = $this->db->query($qstr);

            return ($query && $query->num_rows() > 0) ? $query->row_array()['date_time'] : false;
        } else {
            $qstr = "SELECT NOW() + INTERVAL " . $days . " DAY AS date_time";
            $query = $this->db->query($qstr);

            if ($query && $query->num_rows() > 0) {
                return empty($php_date_format) ? $query->row_array()['date_time'] : reformat_str_date($query->row_array()['date_time'], 'Y-m-d H:i:s', $php_date_format);
            } else {
                return false;
            }
        }
    }

    public function interval_minus_days($days, $is_epoch = true, $php_date_format = "")
    {
        if (!is_numeric($days)) {
            return false;
        }

        if ($is_epoch) {
            $qstr = "SELECT UNIX_TIMESTAMP(NOW() - INTERVAL " . $days . " DAY) AS date_time";
            $query = $this->db->query($qstr);

            return ($query && $query->num_rows() > 0) ? $query->row_array()['date_time'] : false;
        } else {
            $qstr = "SELECT NOW() - INTERVAL " . $days . " DAY AS date_time";
            $query = $this->db->query($qstr);

            if ($query && $query->num_rows() > 0) {
                return empty($php_date_format) ? $query->row_array()['date_time'] : reformat_str_date($query->row_array()['date_time'], 'Y-m-d H:i:s', $php_date_format);
            } else {
                return false;
            }
        }
    }

    public function convert_epoch($timestamp, $php_date_format = "")
    {
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return null;
        }

        $qstr = (int) $timestamp >= 0 ? "SELECT FROM_UNIXTIME(" . $timestamp . ") AS date_time" : "SELECT FROM_UNIXTIME(0) + INTERVAL " . $timestamp . " SECOND AS date_time";
        $query = $this->db->query($qstr);

        if (empty($php_date_format)) {
            return ($query && $query->num_rows() > 0) ? $query->row_array()['date_time'] : null;
        } else {
            if ($query && $query->num_rows() > 0) {
                return reformat_str_date($query->row_array()['date_time'], 'Y-m-d H:i:s', $php_date_format);
            } else {
                return null;
            }
        }
    }

    public function convert_iso8601($timestamp, $php_date_format = "")
    {
        if (empty($timestamp)) {
            return null;
        }

        $now = new DateTime($timestamp);
        return $this->convert_epoch($now->format('U'), $php_date_format);
    }

    public function get_php_tz_offset($timezone)
    {
        $tz = new DateTimeZone($timezone);
        $offset = $tz->getOffset(new DateTime);
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));
        return $offset_prefix . $offset_formatted;
    }

    public function get_system_offset()
    {
        $qstr = "SELECT TIME_FORMAT(TIMEDIFF(NOW(), UTC_TIMESTAMP()), '%H:%i') AS offset";
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            $offset = $query->row_array()['offset'];
            if (preg_match('/^\-/', $offset)) {
                return $offset;
            } else {
                return "+" . $offset;
            }
        } else {
            return "+00:00";
        }
    }

    public function get_financial_year()
    {
        $qstr = "SELECT
                CASE WHEN MONTH(NOW())>=7 THEN concat(YEAR(NOW()))
                ELSE concat(YEAR(NOW())-1) END AS financial_year";
        $query = $this->db->query($qstr);

        return ($query && $query->num_rows() > 0) ? $query->row_array()['financial_year'] : null;
    }
}
