<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('asset_url')) {

    /**
     * Asset_URL
     *
     * Return path of assets (css,js,etc..)
     *
     * @param   none
     * @return  string
     */
    function asset_url()
    {
        return base_url() . 'assets/';
    }
}


if (!function_exists('get_browser_agent')) {

    /**
     * Get_Browser_Agent
     *
     * wrapper for checking browser agents
     *
     * @param   none
     * @return  string
     */
    function get_browser_agent()
    {
        $CI = & get_instance();
        $CI->load->library('user_agent');

        if ($CI->agent->is_browser()) {
            $agent = $CI->agent->browser() . ' ' . $CI->agent->version();
        } elseif ($CI->agent->is_robot()) {
            $agent = $CI->agent->robot();
        } elseif ($CI->agent->is_mobile()) {
            $agent = $CI->agent->mobile();
        } else {
            $agent = 'Unidentified';
        }

        return $agent;
    }
}


if (!function_exists('reformat_str_date')) {

    /**
     * Reformat_String_Date
     *
     * reformat date from one format to another
     *
     * @param   string
     * @param   string
     * @param   string
     * @return  string
     */
    function reformat_str_date($datestr, $from, $to)
    {
        $dateObj = DateTime::createFromFormat($from, $datestr);
        return isset($dateObj) && is_object($dateObj) ? $dateObj->format($to) : "";
    }
}


if (!function_exists('tidy_html_code')) {

    /**
     * Tidy_Html_Code
     *
     * Removes excess characters and spaces from your html
     *
     * @param   string
     * @return  string
     */
    function tidy_html_code($html_str)
    {
        $tidy = new tidy();
        if ($tidy->parseString($html_str)) {
            if ($tidy->cleanRepair()) {
                return $tidy;
            }
        }

        return $html_str;
    }
}


if (!function_exists('array_to_xml')) {

    /**
     * array_to_xml
     *
     * Converts array of any depth to xml document
     * Source: http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml
     *
     * @param   SimpleXMLElement object
     * @param   string
     * @param   string
     * @return  string
     */
    function array_to_xml($student_info, &$xml_student_info)
    {
        foreach ($student_info as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_student_info->addChild("$key");
                    array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_student_info->addChild("item$key");
                    array_to_xml($value, $subnode);
                }
            } else {
                $xml_student_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}


if (!function_exists('partition_array')) {
    function partition_array($list, $p)
    {
        $listlen = count($list);
        $partlen = floor($listlen / $p);
        $partrem = $listlen % $p;
        $partition = [];
        $mark = 0;
        for ($px = 0; $px < $p; $px++) {
            $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
            $partition[$px] = array_slice($list, $mark, $incr);
            $mark += $incr;
        }
        return $partition;
    }
}


if (!function_exists('better_crypt')) {
    function better_crypt($input, $rounds = 7)
    {
        $salt = "";
        $salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        return crypt($input, sprintf('$2a$%02d$', $rounds) . $salt);
    }
}



if (!function_exists('stringify_condition')) {
    function stringify_condition($lhs, $operator, $rhs)
    {
        $CI = & get_instance();

        switch ($operator) {
            case QUERY_FILTER_CONTAINS: return $lhs . " LIKE " . $CI->db->escape('%' . $rhs . '%');
            case QUERY_FILTER_NOT_CONTAINS: return $lhs . " NOT LIKE " . $CI->db->escape('%' . $rhs . '%');
            case QUERY_FILTER_EQUALS: return $lhs . " = " . $CI->db->escape($rhs);
            case QUERY_FILTER_NOT_EQUALS: return $lhs . " != " . $CI->db->escape($rhs);
            case QUERY_FILTER_GREATER_THAN: return $lhs . " > " . $CI->db->escape($rhs);
            case QUERY_FILTER_GREATER_THAN_OR_EQUAL: return $lhs . " >= " . $CI->db->escape($rhs);
            case QUERY_FILTER_LESS_THAN: return $lhs . " < " . $CI->db->escape($rhs);
            case QUERY_FILTER_LESS_THAN_OR_EQUAL: return $lhs . " <= " . $CI->db->escape($rhs);
            case QUERY_FILTER_IS_EMPTY: return "(" . $lhs . " IS NULL OR " . $lhs . " = '')";
            case QUERY_FILTER_IS_NOT_EMPTY: return "(" . $lhs . " IS NOT NULL AND " . $lhs . " != '')";
            case QUERY_FILTER_IS_LISTED:
                if ([$rhs]) {
                    return $lhs . " IN ('" . implode("','", explode(',', $rhs)) . "')";
                } else {
                    return "1";
                }
                // no break
            case QUERY_FILTER_IS_NOT_LISTED:
                if ([$rhs]) {
                    return $lhs . " NOT IN ('" . implode("','", explode(',', $rhs)) . "')";
                } else {
                    return "1";
                }
                // no break
            default: return "1";
        }
    }
}

if (!function_exists('stringify_age_condition')) {
    function stringify_age_condition($firstKey, $attr, $conditions, $values)
    {
        $age_condition = "";
        $date_of_age_condition = "";

        $age_from = "";
        $age_to = "";

        // age is a multi key condition
        // structure is age key, followed by the date of age key ($firstKey + 1)
        $secondKey = $firstKey + 1;

        foreach ($conditions as $key => $condition) {
            if ($key == $firstKey) {
                switch ($condition) {
                    case QUERY_FILTER_EQUALS:
                        $age_from = " BETWEEN ".$values[$key]['age_single']. " AND ".$values[$key]['age_single'];
                        $age_to = $age_from;
                        break;
                    case QUERY_FILTER_IS_BETWEEN:
                        $age_from = " BETWEEN ".$values[$key]['age_from']. " AND ".$values[$key]['age_to'];
                        $age_to = $age_from;
                        break;
                    case QUERY_FILTER_GREATER_THAN:
                        $age_from = " > ".$values[$key]['age_single'];
                        $age_to = $age_from;
                        break;
                    case QUERY_FILTER_GREATER_THAN_OR_EQUAL:
                        $age_from = " >= ".$values[$key]['age_single'];
                        $age_to = $age_from;
                        break;
                    case QUERY_FILTER_LESS_THAN:
                        $age_from = " < ".$values[$key]['age_single'];
                        $age_to = $age_from;
                        break;
                    case QUERY_FILTER_LESS_THAN_OR_EQUAL:
                        $age_from = " <= ".$values[$key]['age_single'];
                        $age_to = $age_from;
                        break;
                }
            } else if ($key == $secondKey) {
                $date_single = DateTime::createFromFormat('d/m/Y', $values[$key]['date_single']);
                $date_from = DateTime::createFromFormat('d/m/Y', $values[$key]['date_from']);
                $date_to = DateTime::createFromFormat('d/m/Y', $values[$key]['date_to']);

                switch ($condition) {
                    case QUERY_FILTER_EQUALS:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## ";
                        break;
                    case QUERY_FILTER_IS_BETWEEN:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_from->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_to->format('Y-m-d')."') ##AGE_FROM## ";
                        break;
                    case QUERY_FILTER_GREATER_THAN:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_TO## ";
                        break;
                    case QUERY_FILTER_GREATER_THAN_OR_EQUAL:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_TO## ";
                        break;
                    case QUERY_FILTER_LESS_THAN:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_TO## ";
                        break;
                    case QUERY_FILTER_LESS_THAN_OR_EQUAL:
                        $date_of_age_condition = " TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_FROM## OR TIMESTAMPDIFF(YEAR, tbl_customers.date_of_birth, '".$date_single->format('Y-m-d')."') ##AGE_TO## ";
                    default: $date_of_age_condition = "";
                }
            }
        }

        $query = str_replace("##AGE_FROM##", $age_from, $date_of_age_condition);
        $sql = str_replace("##AGE_TO##", $age_to, $query);

        return $sql;
    }
}

if (!function_exists('stringify_date_condition')) {
    function stringify_date_condition($lhs, $operator, $rhs1, $rhs2 = "")
    {
        $CI = & get_instance();

        if (empty($rhs2)) {
            $rhs2 = $rhs1;
        }

        switch ($operator) {
            case QUERY_FILTER_EQUALS:
            case QUERY_FILTER_IS_BETWEEN:
                $c1 = $lhs . " >= " . $CI->db->escape($rhs1 . " 00:00:00");
                $c2 = $lhs . " <= " . $CI->db->escape($rhs2 . " 23:59:59");
                return "($c1 AND $c2)";
            case QUERY_FILTER_GREATER_THAN:
                return $lhs . " > " . $CI->db->escape($rhs1 . " 23:59:59");
            case QUERY_FILTER_GREATER_THAN_OR_EQUAL:
                return $lhs . " >= " . $CI->db->escape($rhs1 . " 00:00:00");
            case QUERY_FILTER_LESS_THAN:
                return $lhs . " < " . $CI->db->escape($rhs1 . " 00:00:00");
            case QUERY_FILTER_LESS_THAN_OR_EQUAL:
                return $lhs . " <= " . $CI->db->escape($rhs1 . " 23:59:59");
            default: return "1";
        }
    }
}

if (!function_exists('custom_each')) {
    function custom_each(&$arr)
    {
        $key = key($arr);
        $result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
        next($arr);
        return $result;
    }
}


if (!function_exists('cache_buster')) {
    function cache_buster($url)
    {
        $params = [
            'cache_buster' => md5(time())
        ];

        $parsed_url = parse_url($url);

        if (!isset($parsed_url['query'])) {
            $url .= '?' . http_build_query($params);
        } else {
            $url .= '&' . http_build_query($params);
        }

        return $url;
    }
}


if (!function_exists('hateoas_table_pagination_links_arr')) {
    function hateoas_table_pagination_links_arr($uri, $rel, $offset, $limit, $count, $other_params = [])
    {
        $uri .= '?';
        $other_params_str = !empty($other_params) ? '&amp;' . http_build_query($other_params, '', '&amp;') : '';

        $dataset = [];

        //self
        $tmp_link = [];
        $tmp_link['href'] = $uri . 'offset=' . $offset . '&limit=' . $limit . $other_params_str;
        //$tmp_link['rel'] = $rel;

        $dataset['self'] = $tmp_link;

        //prev
        if ($count <= 0 || $offset <= 0) {
            $tmp_link = [];
            $tmp_link['href'] = null;
            //$tmp_link['rel'] = $rel;

            $dataset['prev'] = $tmp_link;
        } else {
            $new_offset = max($offset - $limit, 0);
            $tmp_link = [];
            $tmp_link['href'] = $uri . 'offset=' . $new_offset . '&limit=' . $limit . $other_params_str;
            //$tmp_link['rel'] = $rel;

            $dataset['prev'] = $tmp_link;
        }


        //next
        if ($count <= 0 || ($offset + $limit >= $count)) {
            $tmp_link = [];
            $tmp_link['href'] = null;
            //$tmp_link['rel'] = $rel;

            $dataset['next'] = $tmp_link;
        } else {
            $new_offset = $offset + $limit;
            $tmp_link = [];
            $tmp_link['href'] = $uri . 'offset=' . $new_offset . '&limit=' . $limit . $other_params_str;
            //$tmp_link['rel'] = $rel;

            $dataset['next'] = $tmp_link;
        }


        return $dataset;
    }
}

if (!function_exists('hateoas_generic_link')) {
    function hateoas_generic_link($uri, $rel, $params = [])
    {
        $params = !empty($params) ? '?' . http_build_query($params, '', '&amp;') : '';

        $dataset = [];
        $dataset['href'] = $uri . $params;
        //$dataset['rel'] = $rel;
        return $dataset;
    }
}

if (!function_exists('number_of_weekend_days')) {
    function number_of_weekend_days($startDate, $endDate)
    {
        $weekendDays = 0;
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        for ($i = $startTimestamp; $i <= $endTimestamp; $i = $i + (60 * 60 * 24)) {
            if (date("N", $i) > 5) {
                $weekendDays = $weekendDays + 1;
            }
        }
        return $weekendDays;
    }
}

if (!function_exists('is_weekend')) {
    function is_weekend($date)
    {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }
}

if (!function_exists('add_business_days')) {
    function add_business_days($weekdaystoadd)
    {
        $curdate = new DateTime("now");
        $real_days_to_add = 0;
        while ($weekdaystoadd > 0) {
            $curdate->modify('+1 day');
            $real_days_to_add++;
            //check if current day is business day
            if (!is_weekend($curdate->format("Y-m-d"))) {
                $weekdaystoadd--;
            }
        }
        return $real_days_to_add;
    }
}


/**
 * Recursive dependency resolution
 *
 * @param string $item Item to resolve dependencies for
 * @param array $items List of all items with dependencies
 * @param array $resolved List of resolved items
 * @param array $unresolved List of unresolved items
 * @return array
 */
if (!function_exists('dep_resolve')) {
    function dep_resolve($item, array $items, array $resolved, array $unresolved)
    {
        array_push($unresolved, $item);
        foreach ($items[$item] as $dep) {
            if (!in_array($dep, $resolved)) {
                if (!in_array($dep, $unresolved)) {
                    array_push($unresolved, $dep);
                    list($resolved, $unresolved) = dep_resolve($dep, $items, $resolved, $unresolved);
                } else {
                    ; //throw new \RuntimeException("Circular dependency: $item -> $dep");
                }
            }
        }
        // Add $item to $resolved if it's not already there
        if (!in_array($item, $resolved)) {
            array_push($resolved, $item);
        }
        // Remove all occurrences of $item in $unresolved
        while (($index = array_search($item, $unresolved)) !== false) {
            unset($unresolved[$index]);
        }

        return [$resolved, $unresolved];
    }
}

if (!function_exists('user_have_access_to')) {
    function user_have_access_to($user_menu, $user_acl_whitelist)
    {
        if (isset($user_acl_whitelist[$user_menu])) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('user_have_access_to_at_least_one')) {
    function user_have_access_to_at_least_one($list_of_acl, $user_acl_whitelist)
    {
        $result = array_intersect(array_keys($list_of_acl), $user_acl_whitelist);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('is_datestr_correct_date_format')) {
    function is_datestr_correct_date_format($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}

if (!function_exists('token_exists_in_string')) {
    function token_exists_in_string($token, $input)
    {
        $parsed_tokens = [];
        preg_match_all("/\[(.*?)\]/", $input, $parsed_tokens, PREG_PATTERN_ORDER);

        if (isset($parsed_tokens[0]) && is_array($parsed_tokens[0]) && count($parsed_tokens[0]) > 0) {
            return in_array($token, $parsed_tokens[0]);
        } else {
            return false;
        }
    }
}


if (!function_exists('touchbase_url')) {

    /**
     *  Reset the session vars before going to their own role's session
     *
     *  example: when a manager is accessing a workspace and assuming the role of a Workspace Admin
     *  when he navigates back to his manager pages, he could call this function before going
     *  back to admin pages.
     *
     * @param current_role - is the current value of tbl_partner_agents.role
     * @param intended_role - is the role that is assuming the role
     * @param target_url - where to?
     */
    function touchbase_url($current_role, $intended_role, $target_url)
    {
        $CI = & get_instance();

        if ($current_role != $intended_role) {
            $url = base_url() . 'login/touch-base/?caller=' . $CI->encryption->url_encrypt($target_url);
        } else {
            $url = $target_url;
        }
        return $url;
    }
}

if (!function_exists('controller_starts_with')) {
    function controller_starts_with($input)
    {
        $CI = & get_instance();
        $controller = $CI->router->fetch_class();
        return substr($controller, 0, strlen($input)) === $input;
    }
}

if (!function_exists('route_starts_with')) {
    function route_starts_with($input)
    {
        $CI = & get_instance();
        $cur_ruri_string = $CI->uri->ruri_string();
        return substr($cur_ruri_string, 0, strlen($input)) === $input;
    }
}

if (!function_exists('method_starts_with')) {
    function method_starts_with($input)
    {
        $CI = & get_instance();
        $cur_method = $CI->router->fetch_method();
        return substr($cur_method, 0, strlen($input)) === $input;
    }
}

if (!function_exists('get_ip')) {
    function get_ip()
    {
        // via CLI or Cron
        $CI = & get_instance();
        if ($CI->input->is_cli_request()) {
            return null;
        }

        $http_headers = apache_request_headers();

        if ($http_headers === false) {
            return '127.0.0.1'; // defaull to localhost
        } 

        if (isset($http_headers["X-Forwarded-For"])) {
            // https://docs.aws.amazon.com/elasticloadbalancing/latest/classic/x-forwarded-headers.html
            // AWS ELB, mostly work in PROD
            // If a request goes through multiple proxies, the IP addresses of each successive proxy is listed. This means, the right-most IP address is the IP address of the most recent proxy and the left-most IP address is the IP address of the originating client.
            $ips = explode(',', $http_headers["X-Forwarded-For"]);
            $ips = array_map('trim', $ips);
            return $ips[0];
        }

        if (isset($_SERVER['HTTP_CLIENT_IP']) && array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            // If a request goes through multiple proxies, the IP addresses of each successive proxy is listed. This means, the right-most IP address is the IP address of the most recent proxy and the left-most IP address is the IP address of the originating client.
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ips = array_map('trim', $ips);
            return $ips[0];
        } else {
            if(isset($_SERVER['REMOTE_ADDR'])){
                return $_SERVER['REMOTE_ADDR'];
            }
            
            return '0.0.0.0';
        }
    }
}

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $to, $decimal_places = 1)
    {
        $formulas = [
            'K' => number_format($bytes / 1024, $decimal_places),
            'M' => number_format($bytes / 1048576, $decimal_places),
            'G' => number_format($bytes / 1073741824, $decimal_places)
        ];
        return isset($formulas[$to]) ? $formulas[$to] : 0;
    }
}

if (!function_exists('getRandomAlphaNum')) {
    function getRandomAlphaNum($length = 20)
    {
        $bytes = random_bytes($length);
        return bin2hex($bytes);
    }
}

if (!function_exists('getAcronym')) {
    function getAcronym($words)
    {
        $words = preg_split("/\s+/", $words);
        $acronym = "";

        foreach ($words as $w) {
            $acronym .= mb_substr($w, 0, 1);
        }

        return $acronym;
    }
}

if (!function_exists('format_phonenumber')) {
    function format_phonenumber($phone)
    {
        if (!empty($phone)) {
            //remove spaces, remove non-ascii characters
            $phone = preg_replace('/\s+/', '', $phone);
            $phone = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $phone);
            return $phone;
        } else {
            return null;
        }
    }
}

if (!function_exists('get_fileinfo')) {
    function get_fileinfo($file)
    {

        // get extension
        $parts = pathinfo($file);

        $ext = $parts['extension'];


        if(strpos($ext,'?') != false)
        {
            $tmp_ext = explode('?', $ext);
            $ext = $tmp_ext[0];
        }

        $filename = $parts['basename'];   
        
        if(strpos($filename,'?') != false)
        {
            $tmp_fname = explode('?', $filename);
            $filename = $tmp_fname[0];
        }

        $data = @get_headers($file, true);

        if(!$data)
        return [
            'filename' => 'Unknown',
            'filetype' => '',
            'extension' => '',
            'filesize' => 0
        ];

        $filesize = isset($data['Content-Length'])?(int) $data['Content-Length']:0;
        $filetype =  isset($data['Content-Type'])?$data['Content-Type']:'unknown';
        
        log_message('debug', '[ get_fileinfo ] parts=' . json_encode($parts) . ' at ' . __METHOD__ . ':' . __LINE__);
        return [
            'filename' => $filename,
            'filetype' => $filetype,
            'extension' => $ext,
            'filesize' => $filesize
        ];
    }
}


if (!function_exists('fix_email')) {
    function fix_email($email)
    {
        $email_comps = explode("@", $email);
        $email_comps[0] = rtrim($email_comps[0], '.');
        return implode("@", $email_comps);
    }
}


if (!function_exists('short_description')) {
    function short_description($text, $limit = 100)
    {
        $text = trim(strip_tags($text));
        $textStrLen = strlen($text);
        if($textStrLen > $limit){
            return substr($text, 0, $limit) . "....";
        }else{
            return $text;
        }
    }
}