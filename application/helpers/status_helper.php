<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('mm8_status_badge')) {
    function mm8_status_badge($id)
    {
        $CI = & get_instance();
        $statuses = $CI->config->item('mm8_status_names');

        if (isset($statuses[$id])) {
            return '<span class="badge status-' . $id . '">' . $statuses[$id] . '</span>';
        } else {
            return '';
        }
    }
}

if (!function_exists('mm8_status_label')) {
    function mm8_status_label($id)
    {
        $CI = & get_instance();
        $statuses = $CI->config->item('mm8_status_names');

        if (isset($statuses[$id])) {
            return '<span class="label status-' . $id . '">' . $statuses[$id] . '</span>';
        } else {
            return '';
        }
    }
}

if (!function_exists('mm8_qa_status_badge')) {
    function mm8_qa_status_badge($id)
    {
        $CI = & get_instance();
        $statuses = $CI->config->item('mm8_qa_status_names');

        if (isset($statuses[$id])) {
            return '<span class="badge qa-status-' . $id . '">' . $statuses[$id] . '</span>';
        } else {
            return '';
        }
    }
}

if (!function_exists('mm8_package_types_badge')) {
    function mm8_package_types_badge($id)
    {
        $CI = & get_instance();
        $packagetypes = $CI->config->item('mm8_package_types');

        if (isset($packagetypes[$id]) && $id == PACKAGE_CONNECTIONS) {
            //return '<span class="badge badge-primary"><i class="fa fa-mouse-pointer m-l-xs m-r-xs"></i></span>';
            //return '<span class="badge badge-primary"><i class="fa fa-mouse-pointer m-l-xs m-r-xs"></i></span>';
            //return '<span class=""><i class="fa fa-mouse-pointer m-l-xs m-r-xs"></i></span>';
            return '<a href="#" class="btn btn-xs btn-primary btn-badge btn-outline btn-circle mhub-package-'.$id .'"><i class="fa fa-mouse-pointer"></i></a>';
        //return '<button class="btn btn-primary btn-circle btn-outline" type="button"><i class="fa fa-mouse-pointer"></i></button>';
        } else {//for PACKAGE_CONNECTIONS_PLUS
            //return ''; //'<span class="badge label-warning">' . $packagetypes[$id] . '</span>';
            //return '<span class="badge badge-primary img-circle"><i class="fa fa-headphones"></i></span>';
            //return '<button class="btn btn-primary btn-circle btn-outline" type="button"><i class="fa fa-headphones"></i></button>';
            //return '<span class=""><i class="fa fa-headphones"></i></span>';
            return '<a href="#" class="btn btn-xs btn-primary btn-badge btn-outline btn-circle mhub-package-'.$id .'"><i class="fa fa-headphones"></i></a>';
        }
    }
}

if (!function_exists('mm8_package_types_icon')) {
    function mm8_package_types_icon($id)
    {
        $CI = & get_instance();
        $packagetypes = $CI->config->item('mm8_package_types');

        if (isset($packagetypes[$id]) && $id == PACKAGE_CONNECTIONS) {
            return '<i class="fa fa-mouse-pointer"></i>';
        } else {//for PACKAGE_CONNECTIONS_PLUS
            return '<i class="fa fa-headphones"></i>';
        }
    }
}
