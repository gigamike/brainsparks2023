<?php

defined('BASEPATH') or exit('No direct script access allowed');
include(__dir__ . '/Sms_library_base.php');

class Sms_library_ph extends Sms_library_base
{
    protected $country_code = "+63";

    public function __construct()
    {
        parent::__construct();
    }
}
