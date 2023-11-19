<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if ($this->session->utilihub_hub_session && $this->session->utilihub_hub_landing_page) {
            redirect($this->session->utilihub_hub_landing_page, 'refresh');
        } else {
            redirect('login', 'refresh');
        }
    }
}
