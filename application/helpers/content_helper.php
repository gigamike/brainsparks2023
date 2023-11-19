<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (! function_exists('mm8_strip_tags')) {

    /**
     *  if configured, function will perform aggressive
     *  removal of tags that it did not expect (for example putting iframes in blocks).
     */
    function mm8_strip_tags($data)
    {
        $CI = & get_instance();
        $allowed_tags = $CI->config->item('mm8_allowed_html_tags');

        if ($allowed_tags != null && is_array($allowed_tags)) {
            return strip_tags($data, implode('', $allowed_tags));
        } else {
            return $data;
        }
    } // strip tags
}

if (! function_exists('strip_tags_in_kv')) {
    /**
     *  accepts an array on which both key and values needs to be cleansed
     *  this is to circumvent the possibility of attacker facking the POST request and passing malicious keys
     */
    function strip_tags_in_kv($kv)
    {
        $output = [];
        foreach ($kv as $k=>$v) {
            if (is_array($v)) {
                $output[mm8_strip_tags($k)] = $v;
            } else {
                $output[mm8_strip_tags($k)] = mm8_strip_tags($v);
            }
        }
        return $output;
    }
}
