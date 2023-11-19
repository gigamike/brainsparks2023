<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email_library
{
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

    public function _process_bounce_flags(&$input, $return_result_flags, &$result_flags, $flag_name)
    {
        // get the results, we will get an array of emails with booleans as values
        $result = is_email_blacklisted($input);

        // only include emails which are NOT blacklisted
        $output = [];
        foreach ($result as $email => $is_blacklisted) {
            if ($is_blacklisted == false) {
                $output[] = $email;
            }
        }

        // reconstruct the email addresses after filtering out the blacklisted ones
        $input = implode(", ", $output);

        // if the caller wanted to receive the result_flags, write it
        if ($return_result_flags) {
            $result_flags[$flag_name] = empty($input) ? 1 : 0;
        }
    }

    /**
     * @param bool $return_result_flags - whether to add the flags in return value
     * @param $result_flags - optionally pass a variable that will be filled with result flags
     *                        in case the email sending process failed for whatever reason
     *                        (e.g bounced email)
     */
    public function send($dataset, $inline = false, $return_result_flags = false, &$result_flags = [])
    {
        $this->CI->email->clear(true);

        //from
        $from_name = isset($dataset['from_name']) ? $dataset['from_name'] : "";
        $this->CI->email->from($dataset['from'], $from_name);

        //reply_to
        if (isset($dataset['reply_to']) && !empty($dataset['reply_to'])) {
            $reply_to_name = isset($dataset['reply_to_name']) ? $dataset['reply_to_name'] : "";
            $this->CI->email->reply_to($dataset['reply_to'], $reply_to_name);
        }

        //to
        $to = ENVIRONMENT == "production" ? $dataset['to'] : $this->CI->config->item('mm8_development_email');
        //$to = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['to'];
        $this->CI->email->to($to);

        //cc
        if (isset($dataset['cc']) && !empty($dataset['cc'])) {
            $cc = ENVIRONMENT == "production" ? $dataset['cc'] : $this->CI->config->item('mm8_development_email');
            //$cc = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['cc'];
            $this->CI->email->cc($cc);
        }

        //bcc
        if (isset($dataset['bcc']) && !empty($dataset['bcc'])) {
            $bcc = ENVIRONMENT == "production" ? $dataset['bcc'] : $this->CI->config->item('mm8_development_email');
            //$bcc = ENVIRONMENT == "development" ? $this->CI->config->item('mm8_development_email') : $dataset['bcc'];
            $this->CI->email->bcc($bcc);
        }

        // header
        if (isset($dataset['headers']) && is_array($dataset['headers']) && count($dataset['headers']) > 0) {
            foreach ($dataset['headers'] as $k => $v) {
                $this->CI->email->set_header($k, $v);
            }
        }

        //subject
        $this->CI->email->subject($dataset['subject']);

        //message
        $this->CI->email->message($dataset['html_message']);

        //set alt message
        if (isset($dataset['text_message']) && !empty($dataset['text_message'])) {
            $this->CI->email->set_alt_message($dataset['text_message']);
        }

        //attachment
        if (isset($dataset['attachment']) && !empty($dataset['attachment'])) {
            if (is_array($dataset['attachment'])) {
                foreach ($dataset['attachment'] as $attach) {
                    $this->CI->email->attach($attach);
                }
            } else {
                $this->CI->email->attach($dataset['attachment']);
            }
        }

        $result = $this->CI->email->send(false);
        if (!$result) {
            log_message("error", "EMAIL SENDING FAILED ===> " . $this->CI->email->print_debugger());
        }

        if ($inline === false) {
            //randomly sleep (0-0.5 sec) to avoid sending mails all at the same time
            //this is to avoid SENDING LIMIT set in SMTP server
            usleep(rand(0, 5) * 100000);
        }

        return $result;
    }

    public function send_basic_email($subject, $html_message, $to, $cc = "", $attachment = "", $return_result_flags = false)
    {
        $dataset = [];
        $result_flags = [];

        $dataset['from'] = $this->CI->config->item('mm8_system_noreply_email');
        $dataset['from_name'] = $this->CI->config->item('mm8_system_name');
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['subject'] = $subject;
        $dataset['attachment'] = $attachment;
        $dataset['html_message'] = $this->CI->load->view('html_email/basic_mail', ['contents' => $html_message, 'to' => $to], true);
        $dataset['sent'] = $this->send($dataset, false, $return_result_flags, $result_flags);

        if ($return_result_flags) {
            $dataset = array_merge($dataset, $result_flags);
        }

        return $dataset;
    }

    /*
     *
     * As of  Aug 17, 2020, change the format of subject
     * BEFORE
     * [mhb-8QY1] HUB Test
     * AFTER
     * HUB Test mhb-8QY1
     */

    public function send_customer_email($customerInfo, $from_name, $from, $reply_to, $to, $cc, $bcc, $subject, $html_body, $attachment = "", $return_result_flags = false, $is_send = true)
    {
        $application_status = $partner_data = [];
        $result_flags = [];

        $unsubscribe = "<br><br><center style=\"font-size:12px\">DO NOT REMOVE THIS LINE: [" . $customerInfo->u_code . "].<br>If you no longer wish to receive these emails, <a href=\"" . base_url() . "unsubscribe/application/" . $this->CI->encryption->url_encrypt($customerInfo->u_code) . "\">unsubscribe</center>";

        $html_body .= $unsubscribe;
          
        $dataset = [];
        $dataset['from_name'] = !empty($from_name) ? $from_name : $partner_data['portal_name'];
        $dataset['from'] = $from;
        $dataset['reply_to'] = $reply_to;
        $dataset['to'] = $to;
        $dataset['cc'] = $cc;
        $dataset['bcc'] = $bcc;
        $dataset['customer_id'] = $customerInfo->id;

        $dataset['subject'] = $subject;
        /*
        $tag = $customerInfo->u_code;
        $subject = trim($subject);
        if (substr($subject, (0 - strlen($tag))) == $tag) {
            $dataset['subject'] = $subject;
        } else {
            $dataset['subject'] = $subject . " " . $tag;
        }
        */

        if ($attachment !== "") {
            $dataset['attachment'] = $attachment;
        }

        $dataset['html_message'] = $this->CI->load->view('html_email/plain', ['contents' => $html_body, 'to' => $to], true);
        $dataset['text_message'] = "";

        if ($is_send) {
            //send
            $dataset['status'] = $this->send($dataset, false, $return_result_flags, $result_flags) ? STATUS_OK : STATUS_NG;
            if ($return_result_flags) {
                $dataset = array_merge($dataset, $result_flags);
            }
        }

        return $dataset;
    }

    public function notify_system_failure($message, $to = "")
    {
        if (did_single_email_opted_out($to != "" ? $to : $this->CI->config->item('mm8_development_email'), EMAIL_SUBSCRIPTION_SYSTEM_MAINTENANCE)) {
            return;
        }

        $this->CI->email->from($this->CI->config->item('mm8_system_noreply_email'), $this->CI->config->item('mm8_system_name'));
        $this->CI->email->to($to != "" ? $to : $this->CI->config->item('mm8_development_email'));
        $this->CI->email->subject("[" . $this->CI->config->item('mm8_system_prefix') . "] [" . ENVIRONMENT . "] BACKEND SYSTEMS FAILED");
        $this->CI->email->message($this->CI->load->view('html_email/system_mail', ['message' => $message, 'to' => $to], true));
        return $this->CI->email->send();
    }
}
