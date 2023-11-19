<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Communications_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_sns_notification($message_type, $topic, $data)
    {
        $str_sql = $this->db->insert_string('tbl_sns_notifications', [
            'topic' => $topic,
            'message_type' => $message_type,
            'data' => is_array($data) ? json_encode($data) : $data
        ]);

        $this->db->query($str_sql);
        return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }

    public function is_customer_email_blacklisted($customer_id)
    {
        $output = false;

        $qstr = "SELECT email FROM tbl_customer WHERE id = " . $this->db->escape($customer_id) . " LIMIT 1";

        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            $customer = $query->row_array();
            if ($this->email_exists_in_blacklist_db($customer['email'])) {
                $output = true;
            }
        }

        return $output;
    }

    public function email_exists_in_blacklist_db($email)
    {
        $qstr = "SELECT COUNT(id) as cnt
            FROM tbl_email_blacklist WHERE email = " . $this->db->escape($email);

        $query = $this->db->query($qstr);
        $result = $query && $query->num_rows() > 0 ? (int) $query->row_array()['cnt'] : 0;

        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function blacklist_email($email, $blacklist_type = 1, $bounce_type = 1, $added_by = 'unknown')
    {
        if (!$this->email_exists_in_blacklist_db($email)) {
            $data = [
                'email' => $email,
                'blacklist_type' => $blacklist_type,
                'bounce_type' => $bounce_type,
                'added_by' => $added_by
            ];

            $str_sql = $this->db->insert_string('tbl_email_blacklist', $data);
            $query = $this->db->query($str_sql);
            if (!$query) {
                $error = $this->db->error(); // Has keys 'code' and 'message'
                return (int) $error['code'] === 1062 || (int) $error['code'] === 1586 ? -1 : false;
            } else {
                return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
            }
        } // if doesnt exists
    }

    // black list email

    public function remove_email_blacklist($email)
    {
        $this->db->where('email', $email);
        $this->db->delete('tbl_email_blacklist');
        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function dt_get_blacklist_summary_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(id) AS cnt
        FROM
        tbl_email_blacklist
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function dt_get_blacklist_summary($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "email" => "email",
            "blacklist_type" => "blacklist_type",
            "bounce_type" => "bounce_type",
            "date_added" => "date_added",
            "date_bounced" => "date_last_sent",
            "added_by" => "added_by"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                *
            FROM
            tbl_email_blacklist
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

    public function get_sms_template($action)
    {
        //try given provider first
        $qstr = "SELECT template FROM tbl_template_sms WHERE action = " . $this->db->escape($action) . " LIMIT 1";

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                return $query->row_array()['template'];
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                $qstr = "SELECT template FROM tbl_template_sms WHERE action = " . $this->db->escape($action) . " AND partner_id = " . $this->config->item('mm8_system_default_partner') . " LIMIT 1";

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    return ($query->num_rows() > 0) ? $query->row_array()['template'] : false;
                }
            }

            return false;
        }
    }

    public function get_email_template($action)
    {
        //try given provider first
        $qstr = "SELECT * FROM tbl_template_email WHERE action = " . $this->db->escape($action) . " LIMIT 1";

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                return $query->row_array();
            }

            return false;
        }
    }

  


    /**
     *  NEW SMS AND EMAIL GATEWAY
     *  this will be used by other emails that's not related to the moving service (mhub)
     */
    public function queue_email($dataset)
    {
        if (isset($dataset['attachment']) && is_array($dataset['attachment'])) {
            $dataset['attachment'] = implode("::", $dataset['attachment']);
        }

        $str_sql = $this->db->insert_string('tbl_gateway_email', $dataset);
        return $this->db->query($str_sql);
    }

    public function check_queue_email()
    {
        $qstr = "SELECT * FROM tbl_gateway_email WHERE processed = '0' AND date_processed = '0000-00-00 00:00:00'";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row;
            }
        }

        //update processed to 1 so it wont be picked up in case theres a cron overlap
        if (count($ret_data) > 0) {
            $this->db->set('processed', 1);
            $this->db->where_in('id', array_keys($ret_data));
            if ($this->db->update('tbl_gateway_email') === false) {
                return false;
            }
        }

        return $ret_data;
    }

    public function update_queue_email($jobid, $dataset)
    {
        //update
        $str_sql = $this->db->update_string('tbl_gateway_email', $dataset, "id = " . $jobid);
        return $this->db->simple_query($str_sql) ? true : false;
    }

    public function queue_sms($dataset)
    {
        $str_sql = $this->db->insert_string('tbl_gateway_sms', $dataset);
        return $this->db->query($str_sql);
    }

    public function check_queue_sms()
    {
        $qstr = "SELECT * FROM tbl_gateway_sms WHERE processed = '0' AND date_processed = '0000-00-00 00:00:00'";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ret_data[$row['id']] = $row;
            }
        }

        //update processed to 1 so it wont be picked up in case theres a cron overlap
        if (count($ret_data) > 0) {
            $this->db->set('processed', 1);
            $this->db->where_in('id', array_keys($ret_data));
            if ($this->db->update('tbl_gateway_sms') === false) {
                return false;
            }
        }

        return $ret_data;
    }

    public function update_queue_sms($jobid, $dataset)
    {
        //update
        $str_sql = $this->db->update_string('tbl_gateway_sms', $dataset, "id = " . $jobid);
        return $this->db->simple_query($str_sql) ? true : false;
    }

    //function to return email template actin names array for the category and/or partner
    public function get_email_templates_list($category = null, $partner_id = null)
    {
        if (empty($partner_id)) {
            $partner_id = $this->config->item('mm8_system_default_partner');
        }

        //try given provider first
        $qstr = "SELECT
                action,
                subject,
                html_template,
                text_template
                FROM tbl_template_email
                WHERE partner_id = " . $this->db->escape($partner_id) . "
                AND category = " . $this->db->escape($category);

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $ret_data[$row['action']] = $row['action'];
                }
                return $ret_data;
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                $qstr = "SELECT
                        action,
                        subject,
                        html_template,
                        text_template
                        FROM tbl_template_email
                        WHERE partner_id = " . $this->config->item('mm8_system_default_partner') . "
                        AND category = " . $this->db->escape($category);

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    if ($query->num_rows() > 0) {
                        foreach ($query->result_array() as $row) {
                            $ret_data[$row['action']] = $row['action'];
                        }
                        return $ret_data;
                    }
                }
            }

            return false;
        }
    }

    //function to return email template actin names array for the category and/or partner
    public function get_sms_templates_list($category = null, $partner_id = null)
    {
        if (empty($partner_id)) {
            $partner_id = $this->config->item('mm8_system_default_partner');
        }

        //try given provider first
        $qstr = "SELECT
                action,
                template
                FROM tbl_template_sms
                WHERE partner_id = " . $this->db->escape($partner_id) . "
                AND category = " . $this->db->escape($category);

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        } else {
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $ret_data[$row['action']] = $row['action'];
                }
                return $ret_data;
            } elseif ($partner_id != $this->config->item('mm8_system_default_partner')) {
                $qstr = "SELECT
                        action,
                        template
                        FROM tbl_template_sms
                        WHERE partner_id = " . $this->config->item('mm8_system_default_partner') . "
                        AND category = " . $this->db->escape($category);

                $query = $this->db->query($qstr);
                if (!$query) {
                    return false;
                } else {
                    if ($query->num_rows() > 0) {
                        foreach ($query->result_array() as $row) {
                            $ret_data[$row['action']] = $row['action'];
                        }
                        return $ret_data;
                    }
                }
            }

            return false;
        }
    }

    public function get_all_email_templates_list()
    {
        $ret_data = [];

        $qstr = "SELECT DISTINCT(action) AS action FROM tbl_template_email ORDER BY 1";

        $query = $this->db->query($qstr);
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row['action']);
            }
        }

        return $ret_data;
    }

    public function get_all_sms_templates_list()
    {
        $ret_data = [];

        $qstr = "SELECT DISTINCT(action) AS action FROM tbl_template_sms ORDER BY 1";

        $query = $this->db->query($qstr);
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row['action']);
            }
        }

        return $ret_data;
    }
}
