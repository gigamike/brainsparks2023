<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Credentials\Credentials;
use Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client;
use Aws\Exception\AwsException;
use Aws\ElasticLoadBalancingV2\Exception\ElasticLoadBalancingV2Exception;

class Aws_elb_library
{
    protected $CI;
    private $credentials;
    private $bucket;
    private $s3_client;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->library('email_library');

        $this->credentials = new Credentials($this->CI->config->item('mm8_aws_access_key_id'), $this->CI->config->item('mm8_aws_secret_access_key'));

        $this->elb_client = new ElasticLoadBalancingV2Client([
            'version' => 'latest',
            'region' => $this->CI->config->item('mm8_aws_region'),
            'credentials' => $this->credentials
        ]);
    }

    public function add_ssl_certificate($CertificateArn, $ListenerArn, $IsDefault = false)
    {
        $output = [
            'status' => 'success',
            'message' => 'Certificate Installed'
        ];

        try {
            $result = $this->elb_client->addListenerCertificates([
                'Certificates' => [ // REQUIRED
                    [
                        'CertificateArn' => $CertificateArn
                    ]
                ],
                'ListenerArn' => $ListenerArn,  // REQUIRED
            ]);
            
            $output['data'] = $result->toArray();
            return $output;
        } catch (ElasticLoadBalancingV2Exception $e) {
            $error_message = "Certificate ID: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();

            $output = [
                'status' => 'error',
                'message' => $error_message
            ];
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Certificate ID: " . $certificate_id . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $output = [
                'status' => 'error',
                'message' => $error_message
            ];

            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return $output;
    } // set ssl certificate

    
    public function remove_ssl_certificate($CertificateArn,$ListenerArn, $IsDefault = false){

        $output = [
            'status' => 'success',
            'message' => 'Certificate Uninstalled'
        ];

        try {

            $result = $this->elb_client->removeListenerCertificates([
                'Certificates' => [ // REQUIRED
                    [
                        'CertificateArn' => $CertificateArn
                    ]
                ],
                'ListenerArn' => $ListenerArn,  // REQUIRED
            ]);
            
            $output['data'] = $result->toArray();
            return $output;

        } catch (ElasticLoadBalancingV2Exception $e) {
            $error_message = "Certificate ID: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();

            $output = [
                'status' => 'error',
                'message' => $error_message
            ];
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Certificate ID: " . $certificate_id . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $output = [
                'status' => 'error',
                'message' => $error_message
            ];

            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return $output;
    } // remove ssl certificate


}
