<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Backend_email_gateway_daemon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('communications_model');
        $this->load->library('email_library');
    }

    /*
    *
    * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/uhack2022.gigamike.net/public_html/index.php cli/backend_email_gateway_daemon
    *
    * /usr/local/bin/php /home4/gigamike/uhack2022.gigamike.net/public_html/index.php cli/backend_email_gateway_daemon
    *
     */
    public function index()
    {
        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }

        //GET BACKLOGS
        $dataset = $this->communications_model->check_queue_email();
        if ($dataset === false) {
            $this->email_library->notify_system_failure("Backend_email_gateway_daemon() wasnt able to retrieve and process emails.");
            return;
        }

        foreach ($dataset as $item) {
            $to_email = $item['to'];
            $cc_email = $item['cc'];
            $bcc_email = $item['bcc'];
            
            // send individual email
            $email_data = [];
            $email_data['from'] = $item['from'];
            $email_data['from_name'] = $item['from_name'];
            $email_data['reply_to'] = isset($item['reply_to']) ? $item['reply_to'] : null;
            $email_data['to'] = $to_email;
            $email_data['cc'] = $cc_email;
            $email_data['bcc'] = $bcc_email;
            $email_data['subject'] = $item['subject'];
            $email_data['html_message'] = $item['html_message'];
            $email_data['text_message'] = $item['text_message'];
            if ($item['attachment'] !== null && $item['attachment'] !== "") {
                $email_data['attachment'] = explode("::", $item['attachment']);
            }


            //send and update
            $result = [];
            $result_flags = [];

            $result['date_processed'] = date('Y-m-d H:i:s');
            $result['processed'] = 2;
            $result['status'] = $this->email_library->send($email_data, false, true, $result_flags) ? STATUS_OK : STATUS_NG;

            // add the bounce email flags
            $result = array_merge($result, $result_flags);

            $this->communications_model->update_queue_email($item['id'], $result);
        }
    }
}

//end of Backend_email_gateway_daemon()
