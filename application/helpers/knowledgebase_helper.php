<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_kb_explainer')) {

    /**
     * get_kb_explainer
     *
     * wrapper for checking browser agents
     *
     * @param	none
     * @return	string
     */
    function get_kb_explainer($section_name, $show = true)
    {
        $CI = & get_instance();
        $retstr = "";
        $prefix = $CI->config->item('mm8_product_code');

        if (isset($CI->config->item($prefix . '_section_explainer')[$section_name])) {
            $retstr .= '<div id="kbExplainer-' . $section_name . '" class="kb-alert alert panel-kb alert-dismissable" attr-section="' . $section_name . '" style="display:' . ($show ? "block" : "none") . '">';
            $retstr .= '<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>';

            if (!empty($CI->config->item($prefix . '_section_explainer')[$section_name]['embed_content_id']) || !empty($CI->config->item($prefix . '_section_explainer')[$section_name]['embed_video_id'])) {
                $retstr .= $CI->config->item($prefix . '_section_explainer')[$section_name]['description'];

                $retstr .= '<div class="m-t-sm">';
                if (!empty($CI->config->item($prefix . '_section_explainer')[$section_name]['embed_content_id']) && !isset($CI->config->item($prefix . '_section_explainer')[$section_name]['external_link'])) {
                    $retstr .= '<a class="btn btn-sm btn-primary m-b-xs" href="javascript:void(0);" onclick="show_embed_content(\'' . $CI->config->item($prefix . '_section_explainer')[$section_name]['embed_content_id'] . '\');">Read More</a>';
                }

                if (!empty($CI->config->item($prefix . '_section_explainer')[$section_name]['embed_video_id']) && !isset($CI->config->item($prefix . '_section_explainer')[$section_name]['external_link'])) {
                    $retstr .= '<a class="btn btn-sm btn-primary m-b-xs" href="javascript:void(0);" onclick="show_embed_video(\'' . $CI->config->item($prefix . '_section_explainer')[$section_name]['embed_video_id'] . '\');">Watch Video</a>';
                }

                if (isset($CI->config->item($prefix . '_section_explainer')[$section_name]['external_link']) && !empty($CI->config->item($prefix . '_section_explainer')[$section_name]['external_link'])) {
                    $retstr .= '<a class="btn btn-sm btn-primary m-b-xs" href="' . $CI->config->item($prefix . '_section_explainer')[$section_name]['external_link'] . '" target="_blank">Read More</a>';
                }

                $retstr .= '</div>';
            } else {
                $retstr .= $CI->config->item($prefix . '_section_explainer')[$section_name]['description'];
            }

            $retstr .= '</div>';
        }

        return $retstr;
    }
}


if (!function_exists('get_kb_toggler')) {

    /**
     * get_kb_toggler
     *
     * wrapper for checking browser agents
     *
     * @param	none
     * @return	string
     */
    function get_kb_toggler($section_name, $show = true)
    {
        $CI = & get_instance();
        $retstr = "";
        $prefix = $CI->config->item('mm8_product_code');

        if (isset($CI->config->item($prefix . '_section_explainer')[$section_name])) {
            $retstr .= '<span id="kbToggler-' . $section_name . '" style="display:' . ($show ? "inline" : "none") . '">';
            $retstr .= '<a class="btn btn-md btn-help m-b-xs" href="javascript:void(0);" onclick="toggle_kb_content(\'' . $section_name . '\');">Read Help</a>';
            $retstr .= '</span>';
        }

        return $retstr;
    }
}

if (!function_exists('get_kb_field_explainer')) {

    /**
     * get_kb_field_explainer
     *
     */
    function get_kb_field_explainer($field_name, $container = 'body', $btn_caption = 'Read Help')
    {
        $CI = & get_instance();
        $retstr = "";
        $prefix = $CI->config->item('mm8_product_code');
        $item = isset($CI->config->item($prefix . '_field_explainer')[$field_name]) ? $CI->config->item($prefix . '_field_explainer')[$field_name] : '';
        $placement = 'left';

        if ($item == '' || empty($item)) {
            return '';
        }

        if (!is_array($item) || count($item) <= 0) {
            return '';
        }

        if (!isset($item['title']) || !isset($item['content'])) {
            return '';
        }

        if (empty($item['content'])) {
            return '';
        }

        if (isset($item['placement']) && !empty($item['placement'])) {
            $placement = $item['placement'];
        }

        $retstr = '<button class="btn btn-md btn-kb-field-toggle" ';
        $retstr .= ' data-container="' . $container . '" data-toggle="popover" data-html="true" data-placement="' .
                $placement . '" data-content="' . $item['content'] . '" title="' . $item['title'] . '"';
        $retstr .= '>';
        $retstr .= $btn_caption;
        $retstr .= '</button">';

        return $retstr;
    }
}
