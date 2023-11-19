<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Log_sms_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
        *,
        DATE_FORMAT(tbl_log_sms.date_sent, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_sent_formatted,
        TIMESTAMPDIFF(HOUR, date_sent, NOW()) AS hours_ago
      ");

        $this->db->from('tbl_log_sms');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['id_not_equal']) && !empty($filter['id_not_equal'])) {
                $this->db->where('id != ', $filter['id_not_equal'], false);
            }
            if (isset($filter['application_id']) && !empty($filter['application_id'])) {
                $this->db->where('application_id', $filter['application_id']);
            }
            if (isset($filter['partner_id']) && !empty($filter['partner_id'])) {
                $this->db->where('partner_id', $filter['partner_id']);
            }
            if (isset($filter['is_inbound'])) {
                $this->db->where('is_inbound', $filter['is_inbound']);
            }
            if (isset($filter['is_check'])) {
                $this->db->where('is_check', $filter['is_check']);
            }
            if (isset($filter['api_message_id']) && !empty($filter['api_message_id'])) {
                $this->db->where('api_message_id', $filter['api_message_id']);
            }
            if (isset($filter['empty_application_id'])) {
                $where = "application_id IS NULL";
                $this->db->where($where);
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
        $query = $this->db->get_where('tbl_log_sms', ['id' => $id]);
        return $query->row();
    }

    public function insert($data)
    {
        //redact url links
        if (isset($data['message']) && !empty($data['message'])) {
            $data['message'] = preg_replace('/http(\S+)/', '{LINK REMOVED}', $data['message']);
        }

        $this->db->insert('tbl_log_sms', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        //redact url links
        if (isset($data['message']) && !empty($data['message'])) {
            $data['message'] = preg_replace('/http(\S+)/', '{LINK REMOVED}', $data['message']);
        }

        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);

        $this->db->update('tbl_log_sms');

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
        return $this->db->delete('tbl_log_sms');
    }

    public function get_sms_correspondence($user_id, $user_role, $order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "application_id" => "tbl_log_sms.application_id",
            "customer_id" => "tbl_customer.u_code",
            "reference_code" => "tbl_application.reference_code",
            "message" => "tbl_log_sms.message",
            "from" => "`tbl_log_sms`.`from`",
            "to" => "`tbl_log_sms`.`to`",
            "date_sent" => "tbl_log_sms.date_sent",
        ];

        $limit_cond = $length >= 0 ? " LIMIT " . $start . "," . $length : "";

        //$order_by = $order_col != NULL ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        switch ($order_col) {
            case null:
                $order_by = "";
                break;
            case "time_to_call":
                if (strtoupper($order_dir) == "DESC") {
                    $order_by = " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) . ", tbl_application.id ASC";
                } else {
                    $order_by = " ORDER BY " . $sort_columns[$order_col] . " IS NULL " . strtoupper($order_dir) . ", " . $sort_columns["$order_col"] . " " . strtoupper($order_dir) . ", tbl_application.id ASC";
                }
                //$order_by .= ", " . $sort_columns["date_modified"] . " DESC";
                break;
            default:
                $order_by = " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir);
                break;
        }

        //PARTNERS WHITELIST BASED ON USER ROLE
        //query to get partners whitelist for user_id
        //this include both partners and resellers whitelists
        $whitelist_join = "";
        if ($user_role != MHUB_MANAGER && $user_role == MHUB_ADMIN) {
            $whitelist_join = " JOIN
                (
                    (
                        SELECT DISTINCT(tbl_user_team_partners.partner_id) AS partner_id
                        FROM tbl_user_team_members
                        JOIN tbl_user_team_partners ON tbl_user_team_partners.team_id = tbl_user_team_members.team_id
                        WHERE tbl_user_team_members.user_id = " . $this->db->escape($user_id) . "
                    )
                    UNION
                    (
                        SELECT DISTINCT(tbl_partner.id) AS partner_id
                        FROM tbl_user_team_members
                        JOIN tbl_user_team_resellers ON tbl_user_team_resellers.team_id = tbl_user_team_members.team_id
                        JOIN tbl_partner ON tbl_partner.reseller_id = tbl_user_team_resellers.reseller_id
                        WHERE tbl_partner.package_type = " . PACKAGE_CONNECTIONS_PLUS . " AND tbl_user_team_members.user_id = " . $this->db->escape($user_id) . "
                    )
                ) AS sudo_tbl_partner ON sudo_tbl_partner.partner_id = tbl_partner.id";
        }

        $qstr = "SELECT
                tbl_log_sms.id AS id,
                tbl_log_sms.is_read AS is_read,
                `tbl_log_sms`.`from` AS `from`,
                `tbl_log_sms`.`to` AS `to`,
                tbl_log_sms.message AS message,
                tbl_log_sms.is_inbound,
                tbl_log_sms.status AS status,
                tbl_log_sms.date_sent,
                tbl_log_sms.status_tag AS status_tag,
                DATE_FORMAT(tbl_log_sms.date_sent, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_sent_formatted,
                TIMESTAMPDIFF(HOUR, date_sent, NOW()) AS hours_ago
            		FROM tbl_log_sms
                LEFT JOIN tbl_application ON tbl_application.id = tbl_log_sms.application_id
            		LEFT JOIN tbl_customer ON tbl_application.customer_id = tbl_customer.id
            		JOIN tbl_partner ON tbl_application.partner_id = tbl_partner.id
                JOIN tbl_reseller ON tbl_partner.reseller_id = tbl_reseller.id
                LEFT JOIN tbl_user as tmp_tbl_user_added ON tmp_tbl_user_added.id = tbl_application.user_added
                LEFT JOIN tbl_user as tmp_tbl_user_assigned ON tmp_tbl_user_assigned.id = tbl_application.user_assigned
                LEFT JOIN tbl_user as tmp_tbl_user_closed ON tmp_tbl_user_closed.id= tbl_application.user_closed
                LEFT JOIN tbl_user as tmp_tbl_user_locked ON tmp_tbl_user_locked.id= tbl_application.user_locked
                " . $whitelist_join . "
                LEFT JOIN tbl_partner_agents ON tbl_application.agent_referred = tbl_partner_agents.id
    		" . $condition . "
    		" . $order_by . $limit_cond;

        // echo $qstr; exit();

        $ret_data = [];

        $query = $this->db->query($qstr);
        //echo $qstr;

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function get_sms_correspondence_count($user_id, $user_role, $condition = "")
    {
        //PARTNERS WHITELIST BASED ON USER ROLE
        //query to get partners whitelist for user_id
        //this include both partners and resellers whitelists
        $whitelist_join = "";
        if ($user_role != MHUB_MANAGER && $user_role != MHUB_ADMIN) {
            $whitelist_join = "JOIN
                (
                    (
                        SELECT DISTINCT(tbl_user_team_partners.partner_id) AS partner_id
                        FROM tbl_user_team_members
                        JOIN tbl_user_team_partners ON tbl_user_team_partners.team_id = tbl_user_team_members.team_id
                        WHERE tbl_user_team_members.user_id = " . $this->db->escape($user_id) . "
                    )
                    UNION
                    (
                        SELECT DISTINCT(tbl_partner.id) AS partner_id
                        FROM tbl_user_team_members
                        JOIN tbl_user_team_resellers ON tbl_user_team_resellers.team_id = tbl_user_team_members.team_id
                        JOIN tbl_partner ON tbl_partner.reseller_id = tbl_user_team_resellers.reseller_id
                        WHERE tbl_partner.package_type = " . PACKAGE_CONNECTIONS_PLUS . " AND tbl_user_team_members.user_id = " . $this->db->escape($user_id) . "
                    )
                ) AS sudo_tbl_partner ON sudo_tbl_partner.partner_id = tbl_partner.id";
        }

        $qstr = "SELECT
              		COUNT(DISTINCT(tbl_log_sms.id)) AS cnt
              		FROM tbl_log_sms
                  LEFT JOIN tbl_application ON tbl_application.id = tbl_log_sms.application_id
              		LEFT JOIN tbl_customer ON tbl_application.customer_id = tbl_customer.id
              		JOIN tbl_partner ON tbl_application.partner_id = tbl_partner.id
                  JOIN tbl_reseller ON tbl_partner.reseller_id = tbl_reseller.id
                  LEFT JOIN tbl_user as tmp_tbl_user_added ON tmp_tbl_user_added.id = tbl_application.user_added
                  LEFT JOIN tbl_user as tmp_tbl_user_assigned ON tmp_tbl_user_assigned.id = tbl_application.user_assigned
                  LEFT JOIN tbl_user as tmp_tbl_user_closed ON tmp_tbl_user_closed.id= tbl_application.user_closed
                  LEFT JOIN tbl_user as tmp_tbl_user_locked ON tmp_tbl_user_locked.id= tbl_application.user_locked
                  " . $whitelist_join . "
                  LEFT JOIN tbl_partner_agents ON tbl_application.agent_referred = tbl_partner_agents.id
              		" . $condition . "
              		LIMIT 1";

        $query = $this->db->query($qstr);
        if (!$query) {
            return 0;
        } else {
            return $query->num_rows() > 0 ? $query->row_array()['cnt'] : 0;
        }
    }

    public function get_application_smses($filter = [], $order = [], $limit = null, $start = null)
    {
        $this->db->select("
          tbl_log_sms.id AS id,
          tbl_log_sms.is_read AS is_read,
          tbl_log_sms.from AS from,
          tbl_log_sms.to AS to,
          tbl_log_sms.message AS message,
          tbl_log_sms.is_inbound,
          tbl_log_sms.status AS status,
          tbl_log_sms.date_sent,
          DATE_FORMAT(tbl_log_sms.date_sent, '" . $this->config->item('mm8_db_date_format') . " %r') AS date_sent_formatted,
          TIMESTAMPDIFF(HOUR, date_sent, NOW()) AS hours_ago
        ");
        $this->db->from('tbl_log_sms');
        $this->db->join('tbl_application', 'tbl_application.id = tbl_log_sms.application_id', 'left');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['application_id']) && !empty($filter['application_id'])) {
                $this->db->where('application_id', $filter['application_id']);
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

        // print_r($this->db->last_query()); exit();

        return $query->result();
    }

    public function get_applictaion_smses_count($condition = "")
    {
        $this->db->select('COUNT(DISTINCT(tbl_log_sms.id)) AS count_id');
        $this->db->from('tbl_log_sms');
        $this->db->join('tbl_application', 'tbl_application.id = tbl_log_sms.application_id', 'left');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['application_id']) && !empty($filter['application_id'])) {
                $this->db->where('application_id', $filter['application_id']);
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function get_sms_log_summary($order_col = "", $order_dir = "", $start = "", $length = "", $condition = "")
    {
        $application_dashboard_sort_columns = [
            "date_sent" => "tbl_log_sms.date_sent",
            "to" => "tbl_log_sms.to",
            "from" => "tbl_log_sms.from",
            "message" => "tbl_log_sms.message",
        ];

        $order_by = $order_col === "" && $order_dir === "" ? "" : " ORDER BY " . $application_dashboard_sort_columns[$order_col] . " " . strtoupper($order_dir);
        $limit = $start === "" && $length === "" ? "" : " LIMIT " . $start . "," . $length;

        $qstr = "SELECT
                    tbl_log_sms.id AS log_sms_id
                    , tbl_log_sms.to
                    , tbl_log_sms.from
                    , tbl_log_sms.message
                    , tbl_log_sms.date_sent AS date_sent
                  FROM tbl_log_sms
                  INNER JOIN tbl_workflow_builder_log ON tbl_workflow_builder_log.id = tbl_log_sms.workflow_builder_log_id
                  " . $condition . " GROUP BY tbl_log_sms.id " . $order_by . $limit;
        // echo $qstr; exit();
        $query = $this->db->query($qstr);

        $ret_data = [];

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function get_sms_log_count($condition = "")
    {
        $qstr = "SELECT
                  COUNT(DISTINCT(tbl_log_sms.id)) AS cnt
                  FROM tbl_log_sms
                  INNER JOIN tbl_workflow_builder_log ON tbl_workflow_builder_log.id = tbl_log_sms.workflow_builder_log_id
                  " . $condition . "
                  LIMIT 1";
        // echo $qstr; exit();
        $query = $this->db->query($qstr);
        return $query && $query->num_rows() > 0 ? $query->row_array()['cnt'] : 0;
    }

    public function get_workflow_builder_v2_sms_log_summary($order_col = "", $order_dir = "", $start = "", $length = "", $condition = "")
    {
        $application_dashboard_sort_columns = [
            "date_sent" => "tbl_log_sms.date_sent",
            "to" => "tbl_log_sms.to",
            "from" => "tbl_log_sms.from",
            "message" => "tbl_log_sms.message",
        ];

        $order_by = $order_col === "" && $order_dir === "" ? "" : " ORDER BY " . $application_dashboard_sort_columns[$order_col] . " " . strtoupper($order_dir);
        $limit = $start === "" && $length === "" ? "" : " LIMIT " . $start . "," . $length;

        $qstr = "SELECT
                    tbl_log_sms.id AS log_sms_id
                    , tbl_log_sms.to
                    , tbl_log_sms.from
                    , tbl_log_sms.message
                    , tbl_log_sms.date_sent AS date_sent
                  FROM tbl_log_sms
                  INNER JOIN tbl_workflow_builder_log_v2 ON tbl_workflow_builder_log_v2.id = tbl_log_sms.workflow_builder_v2_log_id
                  " . $condition . " GROUP BY tbl_log_sms.id " . $order_by . $limit;
        // echo $qstr; exit();
        $query = $this->db->query($qstr);

        $ret_data = [];

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function get_crm_wfb_sms_log_summary($order_col = "", $order_dir = "", $start = "", $length = "", $condition = "")
    {
        $application_dashboard_sort_columns = [
            "date_sent" => "tbl_log_sms.date_sent",
            "to" => "tbl_log_sms.to",
            "from" => "tbl_log_sms.from",
            "message" => "tbl_log_sms.message",
        ];

        $order_by = $order_col === "" && $order_dir === "" ? "" : " ORDER BY " . $application_dashboard_sort_columns[$order_col] . " " . strtoupper($order_dir);
        $limit = $start === "" && $length === "" ? "" : " LIMIT " . $start . "," . $length;

        $qstr = "SELECT
                    tbl_log_sms.id AS log_sms_id
                    , tbl_log_sms.to
                    , tbl_log_sms.from
                    , tbl_log_sms.message
                    , tbl_log_sms.date_sent AS date_sent
                  FROM tbl_log_sms
                  INNER JOIN tbl_crm_workflow_builder_log ON tbl_crm_workflow_builder_log.id = tbl_log_sms.crm_workflow_builder_log_id
                  " . $condition . " GROUP BY tbl_log_sms.id " . $order_by . $limit;
        // echo $qstr; exit();
        $query = $this->db->query($qstr);

        $ret_data = [];

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function get_workflow_builder_v2_sms_log_count($condition = "")
    {
        $qstr = "SELECT
                  COUNT(DISTINCT(tbl_log_sms.id)) AS cnt
                  FROM tbl_log_sms
                  INNER JOIN tbl_workflow_builder_log_v2 ON tbl_workflow_builder_log_v2.id = tbl_log_sms.workflow_builder_v2_log_id
                  " . $condition . "
                  LIMIT 1";
        // echo $qstr; exit();
        $query = $this->db->query($qstr);
        return $query && $query->num_rows() > 0 ? $query->row_array()['cnt'] : 0;
    }

    public function get_crm_wfb_sms_log_count($condition = "")
    {
        $qstr = "SELECT
                  COUNT(DISTINCT(tbl_log_sms.id)) AS cnt
                  FROM tbl_log_sms
                  INNER JOIN tbl_crm_workflow_builder_log ON tbl_crm_workflow_builder_log.id = tbl_log_sms.crm_workflow_builder_log_id
                  " . $condition . "
                  LIMIT 1";
        // echo $qstr; exit();
        $query = $this->db->query($qstr);
        return $query && $query->num_rows() > 0 ? $query->row_array()['cnt'] : 0;
    }

    public function update_bulk_sms($sms_ids, $data)
    {
        if (!count($sms_ids)) {
            return false;
        }

        //redact url links
        if (isset($data['message']) && !empty($data['message'])) {
            $data['message'] = preg_replace('/http(\S+)/', '{LINK REMOVED}', $data['message']);
        }

        $str_sql = $this->db->update_string('tbl_log_sms', $data, 'id IN (' . implode(',', $sms_ids) . ')');
        $query_result = $this->db->simple_query($str_sql);
        return ($query_result ? true : false);
    }

    public function get_unsubscribed()
    {
        $keywords = $this->config->item('mm8_sms_unsubscribe_keywords');
        $hours_back = $this->config->item('mm8_sms_unsubscribe_hours_back');

        $new_keywords = [];
        foreach ($keywords as $keyword) {
            $new_keywords[] = '[[:<:]]' . $keyword . '[[:>:]]';
        }

        $cond = implode('|', $new_keywords);

        $qstr = " SELECT tbl_log_sms.* FROM tbl_log_sms
                    LEFT JOIN tbl_backend_cron_lastruns ON tbl_log_sms.id = tbl_backend_cron_lastruns.tbl_log_sms_id
                    WHERE is_inbound=1  AND date_sent >= CURDATE() - INTERVAL $hours_back HOUR
                    AND (tbl_log_sms.date_sent > tbl_backend_cron_lastruns.last_run OR tbl_backend_cron_lastruns.last_run IS NULL)
                    AND `message` REGEXP BINARY '$cond'";

        $ret_data = [];

        log_message('debug', '[unsubscribe] qstr=' . $qstr);

        $query = $this->db->query($qstr);
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }
}
