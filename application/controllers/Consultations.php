<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Orhanerday\OpenAi\OpenAi;

class Consultations extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('users_model');
        $this->load->model('openai_chat_channels_model');
        $this->load->model('openai_chat_messages_model');

        $this->load->helper('time_elapsed');

        $this->load->library('openai_library');
    }

    protected function validate_access()
    {
        if (!$this->session->utilihub_hub_session) {
            redirect('login', 'refresh');
        }

        return true;
    }

    public function index()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "consultations";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'Consultations';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('consultations/index', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function chatbot()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "consultations";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
            asset_url() . 'js/consultations/chatbot.js',
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'Consultations';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('consultations/chatbot', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function ajax_chatbot_init(){
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $userProfile = $this->users_model->getById($this->session->utilihub_hub_user_id);
        if (! $userProfile) {
            echo json_encode(['successful' => false, "error" => "Invalid User Profile"]);
            return;
        }

        $dataset = $this->input->post();
        
        $chatChannel = $this->openai_library->getSessionChatChannel();

        $filter = [
            'chat_channel_id' => $chatChannel->id,
        ];
        $countChatChannelMessage = $this->openai_chat_messages_model->getCount($filter);
        if(!$countChatChannelMessage){
            $this->db->trans_begin();

            $chatbot = $this->config->item('mm8_openai_chatbot');

            $data = [
                'chat_channel_id' => $chatChannel->id,
                'first_name' => $chatbot['first_name'],
                'last_name' => $chatbot['last_name'],
                'email' => $chatbot['email'],
                'profile_photo' => isset($chatbot['profile_photo']) && !empty($chatbot['profile_photo']) ? asset_url() . $chatbot['profile_photo'] : asset_url() . "img/default/profile-photo.jpg",
                'message' => "Hello, how may we help you?",
            ];
            $message_id = $this->openai_chat_messages_model->save($data);
            if (!$message_id) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
                return;
            }

            //COMMIT
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
                return;
            }

            $this->db->trans_commit();
        }
       
        echo json_encode([
            'successful' => true,
            'chatChannel' => $chatChannel,
        ]);
        return;
    }

    public function ajax_chatbot_get_chat_messages(){
        $html = null;
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }
  
        $dataset = $this->input->post();

        $chatChannel = $this->openai_library->getSessionChatChannel();
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'chatChannel' => $chatChannel,
                'error' => "Invalid Chat Channel",
            ]);
            return;
        }

        $userProfile = $this->users_model->getById($this->session->utilihub_hub_user_id);
        if (! $userProfile) {
            echo json_encode(['successful' => false, "error" => "Invalid Customer Profile"]);
            return;
        }

        $view_data = [];
        $view_data['userProfile'] = $userProfile;
        
        $filter = [
            'chat_channel_id' => $chatChannel->id,
        ];
        $order = [
            'date_added',
        ];
        $chatMessages = $this->openai_chat_messages_model->fetch($filter, $order);
        $view_data['chatMessages'] = $chatMessages;

        $html = $this->load->view('consultations/section_chatbot_chat_messages', $view_data, true);

        echo json_encode([
            'successful' => true,
            'html' => $html,
        ]);
        return;
    }

    public function ajax_chatbot_message_save(){
        //update time outs since uploads may take time to complete
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $userProfile = $this->users_model->getById($this->session->utilihub_hub_user_id);
        if (!$userProfile) {
            echo json_encode(['successful' => false, "error" => "Invalid Customer Profile"]);
            return;
        }

        $dataset = $this->input->post();
        $message = isset($dataset['message']) ? $dataset['message'] : null;

        if ($message=='') {
            echo json_encode(['successful' => false, 'error' => "Required field message"]);
            return;
        }

        $chatChannel = $this->openai_library->getSessionChatChannel();
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => "Invalid Chat Channel",
            ]);
            return;
        }

        $this->db->trans_begin();

        $data = [
            'chat_channel_id' => $chatChannel->id,
            'user_id' => $userProfile->id,
            'first_name' => $userProfile->first_name,
            'last_name' => $userProfile->last_name,
            'email' => $userProfile->email,
            'profile_photo' => isset($userProfile->profile_photo) && !empty($userProfile->profile_photo) ? $userProfile->profile_photo : asset_url() . "img/default/profile-photo.jpg",
            'message' => $message,
        ];
        $message_id = $this->openai_chat_messages_model->save($data);
        if (!$message_id) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
        return;
    }

    public function ajax_chatbot_response(){
        header('Content-Type: application/json;');
        if (!$this->session->utilihub_hub_session) {
            echo json_encode([]);
            return;
        }

        $userProfile = $this->users_model->getById($this->session->utilihub_hub_user_id);
        if (!$userProfile) {
            echo json_encode(['successful' => false, "error" => "Invalid Customer Profile"]);
            return;
        }

        $dataset = $this->input->post();
      
        $chatChannel = $this->openai_library->getSessionChatChannel();
        if (!$chatChannel) {
            echo json_encode([
                'successful' => false,
                'error' => "Invalid Chat Channel",
            ]);
            return;
        }

        $messages = [];

        $ctr = 0;
        $filter = [
            'chat_channel_id' => $chatChannel->id,
        ];
        $order = [
            'date_added',
        ];
        $chatMessages = $this->openai_chat_messages_model->fetch($filter, $order);
        if(count($chatMessages) > 0){
            foreach($chatMessages as $chatMessage){
                $ctr++;
                if($ctr == 1){
                    $messages[] = [
                         "role" => "system",
                         "content" => $chatMessage->message,
                    ];
                }else{
                    if(is_null($chatMessage->user_id)){
                        $messages[] = [
                             "role" => "assistant",
                             "content" => $chatMessage->message,
                        ];
                    }else{
                        $messages[] = [
                            "role" => "user",
                            "content" => $chatMessage->message,
                        ];
                    }
                }
                
            }
        }

        $open_ai = new OpenAi($this->config->item('mm8_openai_api_key'));
        $opts = [
            'model' => $this->config->item('mm8_openai_language_model'),
            'messages' => $messages,
            'temperature' => 0.2, // Temperature is a parameter that controls the “creativity” or randomness of the text generated by GPT-3. A higher temperature (e.g., 0.7) results in more diverse and creative output, while a lower temperature (e.g., 0.2) makes the output more deterministic and focused.
            'max_tokens' => 200, // tokens or words in the generated text
            'frequency_penalty' => 0, // Repetition Repellant
            'presence_penalty' => 0.3, // The Gatekeeper of New Ideas
        ];

        $complete = $open_ai->chat($opts);
        $d = json_decode($complete);
        $message = $d->choices[0]->message->content;

        $this->db->trans_begin();

        $chatbot = $this->config->item('mm8_openai_chatbot');

        $data = [
            'chat_channel_id' => $chatChannel->id,
            'first_name' => $chatbot['first_name'],
            'last_name' => $chatbot['last_name'],
            'email' => $chatbot['email'],
            'profile_photo' => isset($chatbot['profile_photo']) && !empty($chatbot['profile_photo']) ? asset_url() . $chatbot['profile_photo'] : asset_url() . "img/default/profile-photo.jpg",
            'message' => $message,
        ];
        $message_id = $this->openai_chat_messages_model->save($data);
        if (!$message_id) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        //COMMIT
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            echo json_encode(['successful' => false, 'error' => "Chat message update failed! (ERROR_502)"]);
            return;
        }

        $this->db->trans_commit();

        echo json_encode(['successful' => true]);
        return;
    }

    public function forum()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "consultations";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'Consultations';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('consultations/forum', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function telemedicine()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "consultations";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'Consultations';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('consultations/telemedicine', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }

    public function video()
    {
        if (!$this->validate_access()) {
            redirect('login', 'refresh');
        }

        /*
         *
         * BASIC VIEW SETTINGS
         *
         */
        $view_data = [];
        $view_data['user_menu'] = "consultations";

        /*
         * DEFINE STYLES AND SCRIPTS REQUIRED.
         * THE BASICS ARE ALREADY DEFINED IN THE TEMPLATE HEADER AND FOOTER
         *
         */
        $view_data['styles'] = [
        ];

        $view_data['scripts'] = [
        ];

        /*
         * DEFINE IF YOU HAVE AN ONLOAD FUNCTION IN <body>
         *
         */
        $view_data['onload_call'] = "";

        //kb explainer?
        $kb_code = 'Consultations';
        if (isset($this->session->utilihub_hub_user_settings[$kb_code]) && (int) $this->session->utilihub_hub_user_settings[$kb_code] === STATUS_OK) {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, true);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, false);
        } else {
            $view_data['kb_explainer'] = get_kb_explainer($kb_code, false);
            $view_data['kb_toggler'] = get_kb_toggler($kb_code, true);
        }

        $additional_data = [];

        $this->load->view('template_header', $view_data);
        $this->load->view('template_mainmenu', $view_data);
        $this->load->view('template_submenu', array_merge($view_data, $additional_data));
        $this->load->view('consultations/video', array_merge($view_data, $additional_data));
        $this->load->view('template_footer', $view_data);
    }
}
