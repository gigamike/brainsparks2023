<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class Backend_utilities extends CI_Controller
{
    protected $_pageRows = 500;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('lock_library');
        $this->load->library('curl_library');
    }

    public function index()
    {
        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }
    }

    /*
    *
    * /Applications/MAMP/bin/php/php7.4.26/bin/php /Users/michaelgerardgalon/Sites/hackathon/enstack2023.gigamike.net/public_html/index.php cli/backend_utilities sms
    *
    * /usr/local/bin/php /home4/gigamike/enstack2023.gigamike.net/public_html/index.php cli/backend_utilities sms
    *
     */
    public function sms()
    {
        //make sure this is accessible only via cli
        if (!$this->input->is_cli_request()) {
            $this->load->view('errors/restricted_page');
            return;
        }

        $credentials = new Credentials($this->config->item('mm8_aws_access_key_id'), $this->config->item('mm8_aws_secret_access_key'));

        $snSclient = new SnsClient([
            'region' => $this->config->item('mm8_aws_region'),
            'version' => '2010-03-31',
            'credentials' => $credentials,
        ]);

        $message = 'This message is sent from a Amazon SNS code sample.';
        $phone = '+639086087306';

        try {
            $result = $snSclient->publish([
                'Message' => $message,
                'PhoneNumber' => $phone,
            ]);
            var_dump($result);
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }

        echo 'done';
    }
}

//end of Backend_utilities()
