<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Knowledgebase extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->library('account_manager_library');

        $this->load->library('connect_sd_library');
        $this->connect_sd_library->getSessionChatChannel(CONNECT_SD_APP_HUB); // Connect SD is on all pages, check active/inactive chat channel
    }

    public function ajax_load_subsection_modal()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['html_str' => '']);
            return;
        }

        $this->account_manager_library->access_log();


        $view_data = [];
        $view_data['random_string'] = random_string('alnum', 10);
        $view_data['id'] = $this->input->post('id');
        echo json_encode(['random_string' => $view_data['random_string'], 'html_str' => $this->load->view('knowledge_base/section_embed_subsection_video', $view_data, true)]);
    }

    public function ajax_load_content_modal()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['html_str' => '']);
            return;
        }

        $this->account_manager_library->access_log();


        $view_data = [];
        $view_data['random_string'] = random_string('alnum', 10);
        $view_data['id'] = $this->input->post('id');
        echo json_encode(['random_string' => $view_data['random_string'], 'html_str' => $this->load->view('knowledge_base/section_embed_code_content', $view_data, true)]);
    }

    public function ajax_load_video_modal()
    {
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode(['html_str' => '']);
            return;
        }

        $this->account_manager_library->access_log();


        $view_data = [];
        $view_data['random_string'] = random_string('alnum', 10);
        $view_data['id'] = $this->input->post('id');
        echo json_encode(['random_string' => $view_data['random_string'], 'html_str' => $this->load->view('knowledge_base/section_embed_code_video', $view_data, true)]);
    }
}
