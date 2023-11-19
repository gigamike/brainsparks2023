<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{
    protected $user_group = [USER_ADMIN];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_user_profile($user_id, $fields = [])
    {
        if (empty($user_id)) {
            return false;
        }

        $condition = is_numeric($user_id) ? "id = " . $this->db->escape($user_id) : "u_code = " . $this->db->escape($user_id);
        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_users WHERE " . $condition . " LIMIT 1";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            $ret_data = $query->row_array();
        }
        return $ret_data;
    }

    public function get_user_profile_by_gmail($user_id, $fields = [])
    {
        if (empty($user_id)) {
            return false;
        }

        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_users WHERE id = " . $this->db->escape($user_id) . " LIMIT 1";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            $ret_data = $query->row_array();
        }
        return $ret_data;
    }

    public function set_user_profile($dataset, $user_id = "")
    {
        if (empty($user_id)) {
            //insert
            $str_sql = $this->db->insert_string('tbl_users', $dataset);
            $query = $this->db->query($str_sql);
            if (!$query) {
                $error = $this->db->error(); // Has keys 'code' and 'message'
                return (int) $error['code'] === 1062 || (int) $error['code'] === 1586 ? -1 : false;
            } else {
                return $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
            }
        } else {
            //update
            $this->db->set('date_modified', 'NOW()', false);
            return $this->db->update('tbl_users', $dataset, "id = " . $user_id);
        }
    }

    public function get_user_login($email)
    {
        if (empty($email)) {
            return false;
        }

        //get only necessary columns for login
        $qstr = "SELECT
                tbl_users.id,
                tbl_users.first_name,
                tbl_users.last_name,
                tbl_users.full_name,
                tbl_users.login_method,
                tbl_users.email,
                tbl_users.password,
                tbl_users.google_user_id,
                tbl_users.role,
                tbl_users.active,
                tbl_users.confirmed,
                tbl_users.verified
                FROM tbl_users
                WHERE
                tbl_users.email = " . $this->db->escape($email) . "
                AND tbl_users.role IN (" . implode(",", $this->user_group) . ")";

        $query = $this->db->query($qstr);
        if (!$query) {
            return false;
        }

        if ($query->num_rows() == 1) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function get_user_access($role, $user_id)
    {
        $user_access_group = [USER_ADMIN => []];

        switch ($role) {
            case USER_ADMIN:
                break;
            default:
                break;
        }


        return $user_access_group;
    }

    public function get_user_agent_partners_list($email)
    {
        if (empty($email)) {
            return false;
        }

        //get only necessary columns for login
        $qstr = "SELECT
                tbl_partner.id AS partner_id,
                tbl_partner.name AS partner_name,
                tbl_partner.reference_code AS partner_code
                FROM tbl_users
                LEFT JOIN tbl_partner ON tbl_partner.id = tbl_users.partner_id
                WHERE tbl_users.email = " . $this->db->escape($email) . "
                AND tbl_users.role IN (" . implode(",", $this->user_group) . ")
                AND tbl_partner.active = 1";

        $query = $this->db->query($qstr);
        $dataset = [];

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $dataset[$row['partner_id']] = $row['partner_name'] . " (" . $row['partner_code'] . ")";
            }
        }

        return $dataset;
    }

    public function get_user_agent_profile($email, $partner_id, $fields = [])
    {
        if (empty($email) || empty($partner_id)) {
            return false;
        }

        $columns = count($fields) > 0 ? implode(",", $fields) : "*";
        $qstr = "SELECT " . $columns . " FROM tbl_users WHERE email = " . $this->db->escape($email) . " AND partner_id = " . $this->db->escape($partner_id) . " LIMIT 1";
        $query = $this->db->query($qstr);

        $ret_data = [];
        if ($query && $query->num_rows() > 0) {
            $ret_data = $query->row_array();
        }
        return $ret_data;
    }

    public function user_email_used($email, $role, $partner_id = null, $user_id = null)
    {
        if (empty($email)) {
            return true;
        }

        switch ($role) {
            case USER_ADMIN:
            default:
                return true;
        }


        $query = $this->db->query($qstr);
        return !$query || ($query && $query->num_rows() > 0) ? true : false;
    }

    public function set_last_login($user_id)
    {
        $this->db->set('last_login', 'NOW()', false);
        return $this->db->update('tbl_users', null, "id = " . $user_id);
    }

    public function days_since_password_reset($user_id)
    {
        $qstr = "SELECT DATEDIFF(NOW(), last_password_reset) AS days_since FROM tbl_users WHERE id = " . $this->db->escape($user_id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['days_since'] : null;
    }

    public function set_failed_login_attempts($user_id, $count = null)
    {
        if (is_numeric($count)) {
            $this->db->set('failed_attempts', (int) $count);
        } else {
            $this->db->set('failed_attempts', 'failed_attempts+1', false);
        }
        return $this->db->update('tbl_users', null, "id = " . $user_id);
    }

    public function get_failed_login_attempts($user_id)
    {
        $qstr = "SELECT failed_attempts FROM tbl_users WHERE id = " . $this->db->escape($user_id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? (int) $query->row_array()['failed_attempts'] : 0;
    }

    public function lock_user($user_id, $minutes)
    {
        $this->db->set('lock_expiry', 'NOW() + INTERVAL ' . $minutes . ' MINUTE', false);
        return $this->db->update('tbl_users', null, "id = " . $user_id);
    }

    public function unlock_user($user_id)
    {
        $this->db->set('failed_attempts', 0);
        $this->db->set('lock_expiry', null);
        return $this->db->update('tbl_users', null, "id = " . $user_id);
    }

    public function user_minutes_locked($user_id)
    {
        $qstr = "SELECT TIMESTAMPDIFF(MINUTE,  NOW(), lock_expiry) AS minutes_to_go FROM tbl_users WHERE id = " . $this->db->escape($user_id) . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['minutes_to_go'] : null;
    }

    public function log_audit_trail($user_id, $activity, $metadata = null)
    {
        //$ip_address = $this->input->ip_address();
        $ip_address = get_ip();
        $browser_agent = get_browser_agent();

        $tbl_data = ["user_id" => $user_id, "activity" => $activity, "metadata" => $metadata, "ip_address" => $ip_address, "browser_agent" => $browser_agent];
        $str_sql = $this->db->insert_string('tbl_user_audit_trail', $tbl_data);
        return $this->db->query($str_sql);
    }

    public function update_children_profile($user_id)
    {
        if (empty($user_id)) {
            return false;
        }

        $this->db->set('first_name', 'first_name', false);
        $this->db->set('last_name', 'last_name', false);
        $this->db->set('full_name', 'full_name', false);
        $this->db->set('position', 'position', false);
        $this->db->set('mobile_phone', 'mobile_phone', false);
        $this->db->set('preferred_phone_number', 'preferred_phone_number', false);
        $this->db->set('office_phone', 'office_phone', false);
        $this->db->set('office_extension', 'office_extension', false);
        $this->db->set('about', 'about', false);
        $this->db->set('description', 'description', false);
        $this->db->set('profile_photo', 'profile_photo', false);
        $this->db->set('office_id', 'office_id', false);
        $this->db->set('payment_summary_email_cc', 'payment_summary_email_cc', false);
        $this->db->set('payment_method', 'payment_method', false);
        $this->db->set('prepaid_visa_debit_address', 'prepaid_visa_debit_address', false);
        $this->db->set('prepaid_mastercard_debit_address', 'prepaid_mastercard_debit_address', false);
        $this->db->set('prepaid_mastercard_debit_dob', 'prepaid_mastercard_debit_dob', false);
        $this->db->set('prepaid_mastercard_debit_flybuys_no', 'prepaid_mastercard_debit_flybuys_no', false);
        $this->db->set('abn', 'abn', false);
        $this->db->set('irdn', 'irdn', false);
        $this->db->set('crn', 'crn', false);
        $this->db->set('bank_name', 'bank_name', false);
        $this->db->set('bank_acc_name', 'bank_acc_name', false);
        $this->db->set('bank_acc_no', 'bank_acc_no', false);
        $this->db->set('bank_bsb', 'bank_bsb', false);
        $this->db->set('bank_routing_number', 'bank_routing_number', false);
        $this->db->set('bank_sort_code', 'bank_sort_code', false);
        $this->db->set('paypal_account', 'paypal_account', false);
        $this->db->set('paypal_confirmed', 'paypal_confirmed', false);
        $this->db->set('email_signature', 'email_signature', false);
        $this->db->set('auto_payout_enabled', 'auto_payout_enabled', false);
        $this->db->set('ignore_payout_threshold', 'ignore_payout_threshold', false);

        $this->db->where('parent_id', $user_id);

        return $this->db->update('tbl_users');
    }

    public function update_all_children_profile()
    {
        $qstr = "UPDATE
            tbl_users AS child
            JOIN tbl_users AS parent ON parent.id = child.parent_id
            SET
            child.first_name = parent.first_name,
            child.last_name = parent.last_name,
            child.full_name = parent.full_name,
            child.position = parent.position,
            child.mobile_phone = parent.mobile_phone,
            child.preferred_phone_number = parent.preferred_phone_number,
            child.office_phone = parent.office_phone,
            child.office_extension = parent.office_extension,
            child.about = parent.about,
            child.description = parent.description,
            child.profile_photo = parent.profile_photo,
            child.office_id = parent.office_id,
            child.payment_summary_email_cc = parent.payment_summary_email_cc,
            child.payment_method = parent.payment_method,
            child.prepaid_visa_debit_address  = parent.prepaid_visa_debit_address,
            child.prepaid_mastercard_debit_address = parent.prepaid_mastercard_debit_address,
            child.prepaid_mastercard_debit_dob = parent.prepaid_mastercard_debit_dob,
            child.prepaid_mastercard_debit_flybuys_no = parent.prepaid_mastercard_debit_flybuys_no,
            child.abn = parent.abn,
            child.irdn = parent.irdn,
            child.gst_number = parent.gst_number,
            child.crn = parent.crn,
            child.bank_name = parent.bank_name,
            child.bank_acc_name = parent.bank_acc_name,
            child.bank_acc_no = parent.bank_acc_no,
            child.bank_bsb = parent.bank_bsb,
            child.bank_routing_number = parent.bank_routing_number,
            child.bank_sort_code  = parent.bank_sort_code,
            child.paypal_account = parent.paypal_account,
            child.paypal_confirmed = parent.paypal_confirmed,
            child.email_signature = parent.email_signature,
            child.auto_payout_enabled = parent.auto_payout_enabled,
            child.ignore_payout_threshold = parent.ignore_payout_threshold";
        // echo $qstr;

        $query = $this->db->query($qstr);
        return $query ? true : false;
    }

    /**
     *
     * COMMONS > EMAIL ADDRESS ACCOUNTS
     *
     */
    public function dt_get_email_address_accounts_summary($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "email_address" => "tbl_users_email_address.email_address",
            "verified" => "tbl_users_email_address.verified"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
            tbl_users_email_address.id,
            tbl_users_email_address.email_address,
            tbl_users_email_address.verified,
            DATE_FORMAT(tbl_users_email_address.date_added, '" . $this->config->item('mm8_db_date_format') . "') AS date_added
        FROM tbl_users_email_address
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

    public function dt_get_email_address_accounts_count($condition = "")
    {
        $qstr = "SELECT COUNT(tbl_users_email_address.id) AS cnt FROM tbl_users_email_address " . $condition . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    /**
     *
     * COMMONS > EMAIL ADDRESS ACCOUNTS
     *
     */
    public function dt_get_provider_invites_summary($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "provider_name" => "provider_name",
            "provider_email" => "provider_email",
            "provider_phone" => "provider_phone",
            "date_sent" => "date_added"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
            tbl_partner_provider_invites.id,
            tbl_partner_provider_invites.partner_id,
            tbl_partner_provider_invites.provider_name,
            tbl_partner_provider_invites.provider_email,
            tbl_partner_provider_invites.provider_phone,
            tbl_partner_provider_invites.status,
            DATE_FORMAT(tbl_partner_provider_invites.date_added, '" . $this->config->item('mm8_db_date_format') . "') AS date_sent
            FROM tbl_partner_provider_invites
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

    public function dt_get_provider_invites_count($condition = "")
    {
        $qstr = "SELECT COUNT(id) AS cnt FROM tbl_partner_provider_invites " . $condition . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function set_partner_provider_invite($dataset)
    {
        $str_sql = $this->db->insert_string("tbl_partner_provider_invites", $dataset);
        $query = $this->db->query($str_sql);
        return $query && $this->db->affected_rows() > 0 ? $this->db->insert_id() : false;
    }

    public function get_partner_provider_invite($partner_id, $condition = "")
    {
        $qstr = "SELECT * FROM tbl_partner_provider_invites WHERE partner_id = " . $this->db->escape($partner_id) . $condition;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_provider_plans_count($condition = "", $partner_id, $filterPlanFilter = "")
    {
        $plan_filter_condition = "";
        if (!empty($filterPlanFilter)) {
            // already added
            if ($filterPlanFilter == "1") {
                $plan_filter_condition = " AND tbl_plan.id IN (SELECT plan_id FROM tbl_partner_plans WHERE partner_id = " . $partner_id . ")";
            } elseif ($filterPlanFilter == "2") { //not yet added
                $plan_filter_condition = " AND tbl_plan.id NOT IN (SELECT plan_id FROM tbl_partner_plans WHERE partner_id = " . $partner_id . ")";
            } elseif ($filterPlanFilter == "3") { //plans added in last 7 days
                $plan_filter_condition = " AND tbl_plan.date_added BETWEEN '" . date("Y-m-d", strtotime("-7 days")) . "' AND '" . date("Y-m-d") . "'";
            }
        }

        $condition1 = " AND tbl_provider.visibility = " . PROVIDER_VISIBILITY_PUBLIC . " AND tbl_provider.active = 1";
        $qstr = "SELECT count(*) as cnt FROM (SELECT tbl_plan.id AS cnt FROM tbl_plan JOIN tbl_service ON tbl_service.id = tbl_plan.service_id
                JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id " . $condition . $condition1 . $plan_filter_condition . "
                UNION
                SELECT tbl_plan.id AS cnt
                FROM tbl_plan
                JOIN tbl_service ON tbl_service.id = tbl_plan.service_id
                JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id
                JOIN tbl_provider_partner_whitelist ON tbl_provider_partner_whitelist.provider_id = tbl_provider.id AND tbl_provider_partner_whitelist.partner_id = " . $partner_id . " " . $condition . $plan_filter_condition . " AND tbl_provider.visibility = " . PROVIDER_VISIBILITY_PRIVATE . " AND tbl_provider.active = 1) tt LIMIT 1";
        $query = $this->db->query($qstr);
        return $query && $query->num_rows() > 0 ? $query->row_array()['cnt'] : 0;
    }

    public function dt_get_provider_plans_summary($order_col, $order_dir, $start = 0, $length = 0, $condition = "", $partner_id, $filterPlanFilter = "")
    {
        $plan_filter_condition = "";
        if (!empty($filterPlanFilter)) {
            // already added
            if ($filterPlanFilter == "1") {
                $plan_filter_condition = " AND tbl_plan.id IN (SELECT plan_id FROM tbl_partner_plans WHERE partner_id = " . $partner_id . ")";
            } elseif ($filterPlanFilter == "2") { //not yet added
                $plan_filter_condition = " AND tbl_plan.id NOT IN (SELECT plan_id FROM tbl_partner_plans WHERE partner_id = " . $partner_id . ")";
            } elseif ($filterPlanFilter == "3") { //plans added in last 7 days
                $plan_filter_condition = " AND tbl_plan.date_added BETWEEN '" . date("Y-m-d", strtotime("-7 days")) . "' AND '" . date("Y-m-d") . "'";
            }
        }

        $condition1 = " AND tbl_provider.visibility = " . PROVIDER_VISIBILITY_PUBLIC . " AND tbl_provider.active = 1";

        $sort_columns = [
            "name" => "name",
            "plan_type" => "plan_type",
            "offer_type" => "offer_type",
            "product_code" => "product_code",
            "campaign_code" => "campaign_code",
            "zone_name" => "zone_name",
            "crm_only" => "crm_offer_only",
            "service_name" => "service_name",
            "provider_name" => "provider_name",
            "date_added" => "date_added",
            "active" => "active"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";
        $limit = (int) $length > 0 ? " LIMIT " . $start . "," . $length : "";

        $qstr = "SELECT
                tbl_plan.id,
                tbl_plan.u_code,
        tbl_plan.name,
                tbl_plan.plan_type,
                tbl_plan.offer_type,
                tbl_plan.product_code,
                tbl_plan.campaign_code,
                tbl_plan.crm_offer_only,
                tbl_plan.zone_name,
                DATE_FORMAT(tbl_plan.date_added, '" . $this->config->item('mm8_db_date_format') . "') AS date_added,
                tbl_plan.active,
                tbl_service.name AS service_name,
                tbl_provider.name AS provider_name,
                tbl_provider.visibility,
                tbl_provider.provider_type
        FROM tbl_plan
                JOIN tbl_service ON tbl_service.id = tbl_plan.service_id
                JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id
        " . $condition . $condition1 . $plan_filter_condition;

        $qstr .= " UNION

                SELECT
                tbl_plan.id,
                tbl_plan.u_code,
                tbl_plan.name,
                tbl_plan.plan_type,
                tbl_plan.offer_type,
                tbl_plan.product_code,
                tbl_plan.campaign_code,
                tbl_plan.crm_offer_only,
                tbl_plan.zone_name,
                DATE_FORMAT(tbl_plan.date_added, '%d/%m/%Y') AS date_added,
                tbl_plan.active,
                tbl_service.name AS service_name,
                tbl_provider.name AS provider_name,
                tbl_provider.visibility,
                tbl_provider.provider_type
                FROM tbl_plan
                JOIN tbl_service ON tbl_service.id = tbl_plan.service_id
                JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id
                JOIN tbl_provider_partner_whitelist ON tbl_provider_partner_whitelist.provider_id = tbl_provider.id AND tbl_provider_partner_whitelist.partner_id = " . $partner_id . " " . $condition . $plan_filter_condition . " AND tbl_provider.visibility = " . PROVIDER_VISIBILITY_PRIVATE . " AND tbl_provider.active = 1" . $order_by . $limit;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_plan_manager_providers_total($condition = "", $partner_id, $service_id)
    {
        $qstr = "SELECT count(*) as cnt FROM (SELECT
                tbl_provider.id
                FROM
                tbl_provider
                JOIN tbl_plan ON tbl_plan.provider_id = tbl_provider.id
                WHERE tbl_provider.provider_type = 1 AND tbl_plan.service_id = " . $service_id . " AND tbl_plan.active = 1 AND tbl_plan.crm_offer_only = 0 AND tbl_plan.show_in_directory = 1 AND tbl_provider.active=1
                " . $condition . "
                GROUP BY tbl_provider.id) tt LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function dt_get_plan_manager_providers_summary($order_col, $order_dir, $start = 0, $length = 0, $condition = "", $partner_id, $service_id)
    {
        $sort_columns = [
            "provider_name" => "provider_name"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";
        $limit = (int) $length > 0 ? " LIMIT " . $start . "," . $length : "";

        $qstr = "SELECT
                tbl_provider.id,
                tbl_provider.u_code AS provider_id,
                tbl_provider.name AS provider_name,
                tbl_provider.img_url AS img_url,
                tbl_provider.about AS about,
                tbl_provider.website,
                tbl_provider.address AS address,
                tbl_provider.trading_name AS trading_name
                FROM
                tbl_provider
                JOIN tbl_plan ON tbl_plan.provider_id = tbl_provider.id
                WHERE tbl_provider.provider_type = 1 AND tbl_plan.service_id = " . $service_id . " AND tbl_plan.active = 1 AND tbl_plan.crm_offer_only = 0 AND tbl_plan.show_in_directory = 1 AND tbl_provider.active=1
                " . $condition . "
                GROUP BY tbl_provider.id" . $order_by . $limit;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function add_provider($partner_id, $provider_id)
    {
        $qstr = "INSERT INTO tbl_partner_plans (partner_id, provider_id, plan_id)
                SELECT " . $partner_id . ", provider_id, tbl_plan.id plan_id FROM tbl_plan JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id WHERE tbl_provider.u_code = " . $this->db->escape($provider_id) . " and tbl_plan.active=1 AND tbl_plan.id NOT IN (SELECT plan_id FROM tbl_partner_plans WHERE partner_id = " . $this->db->escape($partner_id) . ")";

        //log_message("debug", $qstr);

        if (!$this->db->simple_query($qstr)) {
            return false;
        } else {
            return true;
        }
    }

    public function remove_provider($partner_id, $provider_id)
    {
        $qstr = "DELETE FROM tbl_partner_plans WHERE plan_id IN (SELECT tbl_plan.id FROM tbl_plan JOIN tbl_provider ON tbl_provider.id = tbl_plan.provider_id WHERE tbl_provider.u_code = " . $this->db->escape($provider_id) . ") AND partner_id = " . $this->db->escape($partner_id);

        log_message("debug", $qstr);

        if (!$this->db->simple_query($qstr)) {
            return false;
        } else {
            return true;
        }
    }

    public function get_total_provider_plans_added($partner_id, $service_id = "", $provider_id)
    {
        $condition = "";
        if (!empty($service_id)) {
            $condition = " AND tbl_plan.service_id=" . $this->db->escape($service_id);
        }
        $qstr = "SELECT count(*) total_listed FROM tbl_partner_plans JOIN tbl_plan ON tbl_plan.id=tbl_partner_plans.plan_id WHERE tbl_plan.active=1" . $condition . " AND tbl_plan.provider_id=" . $this->db->escape($provider_id) . " AND tbl_partner_plans.partner_id = " . $this->db->escape($partner_id);

        $query = $this->db->query($qstr);

        $total_listed = ($query && $query->num_rows() > 0) ? $query->row_array()['total_listed'] : 0;

        $qstr1 = "SELECT count(*) total FROM tbl_plan WHERE tbl_plan.active=1" . $condition . " AND tbl_plan.provider_id=" . $this->db->escape($provider_id);

        $query1 = $this->db->query($qstr1);

        $total = ($query1 && $query1->num_rows() > 0) ? $query1->row_array()['total'] : 0;

        return ['listed' => $total_listed, 'total' => $total];
    }

    public function get_external_providers($partner_id)
    {
        $qstr = "SELECT * FROM tbl_provider WHERE active = 1 AND provider_type = " . PROVIDER_TYPE_EXTERNAL . " AND id NOT IN (SELECT provider_id FROM tbl_provider_partner_whitelist WHERE partner_id=" . $this->db->escape($partner_id) . ")";

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_external_providers_summary($partner_id, $order_col, $order_dir, $start, $length, $condition = "", $postcode = "")
    {
        $extra_condition = "";
        if (!empty($postcode)) {
            $extra_condition = " AND tbl_provider.id IN (SELECT tbl_plan.provider_id
                FROM tbl_plan
                JOIN tbl_provider ON  tbl_provider.id = tbl_plan.provider_id
                LEFT JOIN tbl_plan_availability_postcode_whitelist ON tbl_plan_availability_postcode_whitelist.plan_id = tbl_plan.id
                WHERE tbl_provider.active = 1 AND tbl_provider.visibility = 1 AND tbl_provider.provider_type = 2 AND tbl_plan.active = 1 AND (tbl_plan.cluster = 0 OR (tbl_plan_availability_postcode_whitelist.postcode = " . $this->db->escape($postcode) . ")) )";
        }

        $sort_columns = [
            "provider" => "tbl_provider.name"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                tbl_provider.u_code AS provider_code,
                tbl_provider.name AS provider_name,
                tbl_provider.address AS address,
                tbl_provider.about AS about,
                tbl_provider.img_url AS img_url,
                tbl_provider.website
                FROM
                tbl_provider
                " . (empty($condition) ? "WHERE 1" : $condition) . "
                AND tbl_provider.provider_type = " . PROVIDER_TYPE_EXTERNAL . "
                " . $extra_condition . "
                AND tbl_provider.id NOT IN (SELECT provider_id FROM tbl_provider_partner_whitelist WHERE partner_id=" . $this->db->escape($partner_id) . ")
                AND tbl_provider.id IN (SELECT provider_id FROM tbl_plan WHERE active=1)"
                . $order_by . "
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

    public function dt_get_external_providers_count($partner_id, $condition = "", $postcode = "")
    {
        $extra_condition = "";
        if (!empty($postcode)) {
            $extra_condition = " AND tbl_provider.id IN (SELECT tbl_plan.provider_id
                FROM tbl_plan
                JOIN tbl_provider ON  tbl_provider.id = tbl_plan.provider_id
                LEFT JOIN tbl_plan_availability_postcode_whitelist ON tbl_plan_availability_postcode_whitelist.plan_id = tbl_plan.id
                WHERE tbl_provider.active = 1 AND tbl_provider.visibility = 1 AND tbl_provider.provider_type = 2 AND tbl_plan.active = 1 AND (tbl_plan.cluster = 0 OR (tbl_plan_availability_postcode_whitelist.postcode = " . $this->db->escape($postcode) . ")) )";
        }
        $qstr = "SELECT
                COUNT(tbl_provider.id) AS cnt
                FROM
                tbl_provider
                " . (empty($condition) ? "WHERE 1" : $condition) . "
                AND tbl_provider.provider_type = " . PROVIDER_TYPE_EXTERNAL . "
                " . $extra_condition . "
                AND tbl_provider.id NOT IN (SELECT provider_id FROM tbl_provider_partner_whitelist WHERE partner_id=" . $this->db->escape($partner_id) . ")
                AND tbl_provider.id IN (SELECT provider_id FROM tbl_plan WHERE active=1) LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function dt_get_my_providers_summary($order_col, $order_dir, $start, $length, $condition = "", $partner_id)
    {
        $sort_columns = [
            "provider_contact_person" => "provider_contact_person",
            "provider_name" => "provider_name",
            "provider_email" => "provider_email",
            "provider_phone" => "provider_phone"
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";
        $limit = (int) $length > 0 ? " LIMIT " . $start . "," . $length : "";

        $qstr = "SELECT
                tbl_partner_provider_invites.id invite_whitelist_id,
                'invite' source,
                tbl_provider.img_url,
                tbl_provider.address,
                tbl_provider.about,
                tbl_provider.website,
                tbl_partner_provider_invites.partner_id,
                tbl_partner_provider_invites.provider_id,
                ifnull(tbl_provider.contact_person,CONCAT(tbl_partner_provider_invites.provider_first_name, ' ', tbl_partner_provider_invites.provider_last_name)) provider_contact_person,
                ifnull(tbl_provider.name,tbl_partner_provider_invites.provider_name) provider_name,
                ifnull(tbl_provider.contact_email,tbl_partner_provider_invites.provider_email) provider_email,
                ifnull(tbl_provider.contact_phone,tbl_partner_provider_invites.provider_phone) provider_phone,
                tbl_partner_provider_invites.status
                FROM tbl_partner_provider_invites
                LEFT JOIN tbl_provider ON tbl_provider.id=tbl_partner_provider_invites.provider_id WHERE tbl_partner_provider_invites.partner_id=" . $this->db->escape($partner_id) . "

                UNION

                SELECT
                tbl_provider_partner_whitelist.id invite_whitelist_id,
                'whitelist' source,
                tbl_provider.img_url,
                tbl_provider.address,
                tbl_provider.about,
                tbl_provider.website,
                tbl_provider_partner_whitelist.partner_id,
                tbl_provider_partner_whitelist.provider_id,
                tbl_provider.contact_person provider_contact_person,
                tbl_provider.name provider_name,
                tbl_provider.contact_email provider_email,
                tbl_provider.contact_phone provider_phone,
                tbl_provider_partner_whitelist.whitelisted status
                FROM tbl_provider_partner_whitelist
                JOIN tbl_provider ON tbl_provider.id=tbl_provider_partner_whitelist.provider_id WHERE tbl_provider_partner_whitelist.partner_id=" . $this->db->escape($partner_id) . " AND tbl_provider_partner_whitelist.is_inviter = 0"
                . $order_by . " " . $limit;

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                array_push($ret_data, $row);
            }
        }
        return $ret_data;
    }

    public function dt_get_my_providers_count($condition = "", $partner_id)
    {
        $qstr = "SELECT COUNT(*) AS cnt FROM (SELECT
                'invite' source,
                tbl_partner_provider_invites.partner_id,
                tbl_partner_provider_invites.provider_id,
                tbl_partner_provider_invites.provider_name,
                tbl_partner_provider_invites.provider_email,
                tbl_partner_provider_invites.provider_phone,
                tbl_partner_provider_invites.status
                FROM tbl_partner_provider_invites
                LEFT JOIN tbl_provider ON tbl_provider.id=tbl_partner_provider_invites.provider_id WHERE tbl_partner_provider_invites.partner_id=" . $this->db->escape($partner_id) . "

                UNION

                SELECT
                'whitelist' source,
                tbl_provider_partner_whitelist.partner_id,
                tbl_provider_partner_whitelist.provider_id,
                tbl_provider.name provider_name,
                tbl_provider.contact_email provider_email,
                tbl_provider.contact_phone provider_phone,
                tbl_provider_partner_whitelist.whitelisted status
                FROM tbl_provider_partner_whitelist
                JOIN tbl_provider ON tbl_provider.id=tbl_provider_partner_whitelist.provider_id WHERE tbl_provider_partner_whitelist.partner_id=" . $this->db->escape($partner_id) . " AND tbl_provider_partner_whitelist.is_inviter = 0) tt LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function get_provider_info_by_email($email)
    {
        $qstr = "SELECT tbl_provider.* FROM tbl_provider JOIN tbl_user_marketplace ON tbl_user_marketplace.id = tbl_provider.manager_id WHERE tbl_user_marketplace.email = " . $this->db->escape($email) . " LIMIT 1";
        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array() : [];
    }

    public function get_provider_plans($provider_id, $service_id = "")
    {
        if (!empty($service_id)) {
            $qstr = "SELECT * FROM tbl_plan WHERE active = 1 AND provider_id = " . $this->db->escape($provider_id) . " AND service_id = " . $this->db->escape($provider_id);

            $ret_data = [];
            $query = $this->db->query($qstr);

            if ($query && $query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    array_push($ret_data, $row);
                }
            }
            return $ret_data;
        } else {
            $qstr = "SELECT * FROM tbl_plan WHERE active = 1 AND provider_id = " . $this->db->escape($provider_id);

            $ret_data = [];
            $query = $this->db->query($qstr);

            if ($query && $query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    array_push($ret_data, $row);
                }
            }
            return $ret_data;
        }
    }

    public function provider_industries($indexed_by_id = false)
    {
        $qstr = "SELECT * FROM tbl_lookup_industry WHERE active = 1 AND industry_type = 1 ORDER BY display_order DESC";

        $ret_data = [];
        $query = $this->db->query($qstr);

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if ($indexed_by_id) {
                    $ret_data[$row['id']] = $row;
                } else {
                    array_push($ret_data, $row);
                }
            }
        }
        return $ret_data;
    }

    /*
     * AMS
     */

    public function ams_get_user_login($email)
    {
        if (empty($email)) {
            return false;
        }

        //get only necessary columns for login
        $this->db->select('id,
                            first_name,
                            last_name,
                            full_name,
                            login_method,
                            email,
                            password,
                            google_user_id,
                            role,
                            active,
                            confirmed,
                            verified');
        $this->db->from('tbl_users');
        $this->db->where('email', $email);
        $this->db->where('role', USER_ACCOUNT_MANAGER);
        $this->db->limit(1);

        $query = $this->db->get();

        // print_r($this->db->last_query());

        if (!$query) {
            return false;
        }

        if ($query->num_rows() == 1) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function ams_map_target($user_id)
    {
        $this->db->select('id
                            , default_role
                            , is_role_admin
                            , is_role_internal_sales
                            , is_role_external_sales
                            , is_role_client_success');
        $this->db->from('tbl_account_manager');
        $this->db->where('account_manager_agent', $user_id);
        $this->db->limit(1);

        $query = $this->db->get();

        // print_r($this->db->last_query());

        return ($query && $query->num_rows() > 0) ? $query->row_array() : null;
    }

    public function fetch($filter = [], $order = [], $limit = null, $start = null, $fields = [])
    {
        if (count($fields) > 0) {
            $this->db->select(implode(',', $fields));
        }

        $this->db->from('tbl_users');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['ids']) && count($filter['ids']) > 0) {
                $this->db->where_in('id', $filter['ids']);
            }
            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }
            if (isset($filter['active'])) {
                $this->db->where('active', $filter['active']);
            }
            if (isset($filter['confirmed'])) {
                $this->db->where('confirmed', $filter['confirmed']);
            }
            if (isset($filter['email']) && !empty($filter['email'])) {
                $this->db->where('email', $filter['email']);
            }
            if (isset($filter['role']) && !empty($filter['role'])) {
                $this->db->where('role', $filter['role']);
            }
            if (isset($filter['roles']) && count($filter['roles']) > 0) {
                $this->db->where_in('role', $filter['roles']);
            }
            if (isset($filter['mobile_phones']) && count($filter['mobile_phones']) > 0) {
                $this->db->where_in('mobile_phone', $filter['mobile_phones']);
            }
            if (isset($filter['sendy_date'])) {
                $this->db->group_start();
                $this->db->where("date_added >= '" . $filter['sendy_date'] . "'");
                $this->db->or_where("date_modified >= '" . $filter['sendy_date'] . "'");
                $this->db->group_end();
            }
            if (isset($filter['date_added_between'])) {
                $this->db->where("DATE(date_added) BETWEEN '" . $filter['date_added_between']['start_date'] . "' AND '" . $filter['date_added_between']['end_date'] . "'");
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
        $this->db->select('COUNT(tbl_users.id) AS count_id');
        $this->db->from('tbl_users');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
            if (isset($filter['id']) && !empty($filter['id'])) {
                $this->db->where('id', $filter['id']);
            }
            if (isset($filter['ids']) && count($filter['ids']) > 0) {
                $this->db->where_in('id', $filter['ids']);
            }
            if (isset($filter['id_not']) && !empty($filter['id_not'])) {
                $this->db->where('id != ', $filter['id_not']);
            }
            if (isset($filter['active'])) {
                $this->db->where('active', $filter['active']);
            }
            if (isset($filter['confirmed'])) {
                $this->db->where('confirmed', $filter['confirmed']);
            }
            if (isset($filter['partner_id']) && !empty($filter['partner_id'])) {
                $this->db->where('partner_id', $filter['partner_id']);
            }
            if (isset($filter['email']) && !empty($filter['email'])) {
                $this->db->where('email', $filter['email']);
            }
            if (isset($filter['role']) && !empty($filter['role'])) {
                $this->db->where('role', $filter['role']);
            }
            if (isset($filter['roles']) && count($filter['roles']) > 0) {
                $this->db->where_in('role', $filter['roles']);
            }
            if (isset($filter['mobile_phones']) && count($filter['mobile_phones']) > 0) {
                $this->db->where_in('mobile_phone', $filter['mobile_phones']);
            }
            if (isset($filter['sendy_date'])) {
                $this->db->group_start();
                $this->db->where("date_added >= '" . $filter['sendy_date'] . "'");
                $this->db->or_where("date_modified >= '" . $filter['sendy_date'] . "'");
                $this->db->group_end();
            }
            if (isset($filter['date_added_between'])) {
                $this->db->where("DATE(date_added) BETWEEN '" . $filter['date_added_between']['start_date'] . "' AND '" . $filter['date_added_between']['end_date'] . "'");
            }
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result['count_id'];
    }

    public function getById($id)
    {
        $query = $this->db->get_where('tbl_users', ['id' => $id]);
        return $query->row();
    }

    public function getByEmail($email)
    {
        $query = $this->db->get_where('tbl_users', ['email' => $email]);
        return $query->row();
    }

    public function getMin($field, $filter = [])
    {
        $this->db->select_min($field);
        $this->db->from('tbl_users');

        if (!is_null($filter) && is_array($filter) && count($filter) > 0) {
        }

        $query = $this->db->get();

        // print_r($this->db->last_query());

        $result = $query->row_array();
        return $result[$field];
    }

    public function get_all_agents_list($active_only = false, $roles = [])
    {
        $cond1 = $active_only ? " WHERE tbl_users.active = 1 AND tbl_users.verified = 1" : "WHERE 1";
        $cond2 = is_array($roles) && !empty($roles) ? " AND tbl_users.role IN (" . implode(",", $roles) . ")" : "";

        //show all
        $qstr = "SELECT
            tbl_users.id AS user_id,
            tbl_users.first_name AS first_name,
            tbl_users.last_name AS last_name,
            tbl_users.email AS email
            FROM tbl_users
            " . $cond1 . $cond2 . "
            ORDER BY tbl_users.full_name";

        $query = $this->db->query($qstr);
        $ret_data = [];

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                //$ret_data[$row['user_id']] = $row['full_name'] . ' (' . $row['email'] . ')';
                //$len = strpos($row['email'], '@') === false ? 10 : strpos($row['email'], '@') + 8;
                //$ret_data[$row['user_id']] = $row['full_name'] . ' (' . substr($row['email'], 0, $len) . '..)';
                $ret_data[$row['user_id']] = mb_substr($row['first_name'], 0, 1) . ' ' . $row['last_name'] . ' (' . $row['email'] . ')';
            }
        }

        return $ret_data;
    }

    public function dt_get_users($order_col, $order_dir, $start, $length, $condition = "")
    {
        $sort_columns = [
            "u_code" => "tbl_users.u_code",
            "first_name" => "tbl_users.first_name",
            "last_name" => "tbl_users.last_name",
            "email" => "tbl_users.email",
            "mobile_phone" => "tbl_users.mobile_phone",
            "position" => "tbl_users.position",
            "date_added" => "tbl_users.date_added",
        ];

        $order_by = $order_col != null ? " ORDER BY " . $sort_columns[$order_col] . " " . strtoupper($order_dir) : "";

        $qstr = "SELECT
                    tbl_users.id
                    , tbl_users.u_code
                    , tbl_users.first_name
                    , tbl_users.last_name
                    , tbl_users.email
                    , tbl_users.mobile_phone
                    , tbl_users.position
                    , DATE_FORMAT(tbl_users.date_added, '" . $this->config->item('mm8_db_date_format') . " %H:%i') AS date_added
        FROM tbl_users
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

    public function dt_get_users_count($condition = "")
    {
        $qstr = "SELECT
        COUNT(tbl_users.id) AS cnt
        FROM tbl_users
        " . $condition . "
        LIMIT 1";

        $query = $this->db->query($qstr);
        return ($query && $query->num_rows() > 0) ? $query->row_array()['cnt'] : 0;
    }

    public function save($data)
    {
        if (isset($data['id'])) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    public function insert($data)
    {
        $this->db->insert('tbl_users', $data);
        return $this->db->insert_id();
    }

    private function update($data)
    {
        foreach ($data as $key => $value) {
            $this->db->set($key, $value);
        }
        $this->db->where('id', $data['id']);
        $this->db->update('tbl_users');

        return $data['id'];
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('tbl_users');
    }
}
