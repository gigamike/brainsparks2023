<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Form_validator
{
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

    public function validateEmail($email)
    {
        $email = html_entity_decode($email);
        if (empty($email) || preg_match('/^[a-zA-Z0-9_+&*-]+(?:\.[a-zA-Z0-9_+&*-]+)*@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,7}$/', $email)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateTitle($title)
    {
        $title = html_entity_decode($title);
        if (empty($title) || preg_match('/^[a-zA-Z]+$/', $title)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateName($name)
    {
        $name = html_entity_decode($name);
        if (empty($name) || preg_match('/^[a-zA-Z]+(([\',. -][a-zA-Z ])?[a-zA-Z]*)*$/', $name)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateMobile($mobile, $length = 10)
    {
        $mobile = html_entity_decode($mobile);
        $mobile = str_replace(["+", " "], "", $mobile);
        if (empty($mobile) || preg_match('/^[0-9]{' . $length . '}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateAlphabetsOnly($string)
    {
        $string = html_entity_decode($string);
        if (empty($string) || preg_match('/^[a-zA-Z]+$/', $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateSafetext($string)
    {
        if (is_array($string)) {
            foreach ($string as $str) {
                $string = html_entity_decode($str);
                if (empty($string) || preg_match('/^[a-zA-Z0-9 _\.\,\-\+\\\\\'\\\\"\/\n\r&@,:;\{\}\[\]\(\)!]+$/', $string)) {
                    continue;
                } else {
                    return false;
                }
            }
            return true;
        } else {
            $string = html_entity_decode($string);
            if (empty($string) || preg_match('/^[a-zA-Z0-9 _\.\,\-\+\\\\\'\\\\"\/\n\r&@,:;\{\}\[\]\(\)!]+$/', $string)) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function validateBaseSixtyFour($string)
    {
        $string = html_entity_decode($string);
        if (empty($string) || preg_match('/^[a-zA-Z0-9-_]*~{0,2}$/', $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateNumber($string)
    {
        $string = html_entity_decode($string);
        if ($string == "" || $string == null || preg_match('/^[0-9]+(\.[0-9]+)?$/', $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateDate($string)
    {
        $string = html_entity_decode($string);
        if (empty($string) || preg_match('/^([0][1-9]|[1|2][0-9]|[3][0|1])[.\/-]([0][1-9]|[1][0-2])[.\/-]([0-9]{4})$/', $string)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateJson($string)
    {
        $string = html_entity_decode($string);
        if (empty($string) || json_decode($string) !== null) {
            return true;
        } else {
            return false;
        }
    }

    public function validateSeriliasedObject($string)
    {
        $string = html_entity_decode($string);
        if (empty($string) || unserialize($string) !== false) {
            return true;
        } else {
            return false;
        }
    }
}
