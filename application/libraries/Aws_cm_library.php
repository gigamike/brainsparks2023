<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Credentials\Credentials;
use Aws\Acm\AcmClient;
use Aws\Exception\AwsException;
use Aws\Acm\Exception\AcmException;

class Aws_cm_library
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

        $this->acm_client = new AcmClient([
            'version' => 'latest',
            'region' => $this->CI->config->item('mm8_aws_region'),
            'credentials' => $this->credentials
        ]);
    }

    public function request_certificate(
        $domain,
        $domain_validation_options,
        $token,
        $ctl_preference = 'ENABLED',
        $tags = [[ 'Key' => 'issued_via', 'Value' => 'codeigniter_app']],
        $validation_method = 'DNS',
        $SAN = []
    ) {
        try {

            $params = [
                'DomainName' => $domain,
                'DomainValidationOptions' => $domain_validation_options,
                'IdempotencyToken' => $token,
                'Options' => [
                    'CertificateTransparencyLoggingPreference' => $ctl_preference,
                ],
                'Tags' => $tags,
                'ValidationMethod' => $validation_method,
            ];

            if(is_array($SAN) && count($SAN)> 0)
                $params['SubjectAlternativeNames'] = $SAN;

            $result = $this->acm_client->requestCertificate($params);

            return $result;
        } catch (AcmException $e) {
            $error_message = "Domain: " . $domain . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Domain: " . $domain . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return false;
    } // request certificate

    public function describe_certificate($CertificateArn)
    {
        $output = false;

        try {
            $result = $this->acm_client->describeCertificate([
                'CertificateArn' => $CertificateArn,
            ]);
            $output = $result;
            return $output;
        } catch (AcmException $e) {
            $error_message = "CertificateArn: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "CertificateArn: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return $output;
    } // describe certificate
     
    public function delete_certificate($CertificateArn)
    {
        $output = [
            'status' => 'success',
            'message' => 'Certificate Deleted'
        ];

        try {
            $this->acm_client->deleteCertificate([
                'CertificateArn' => $CertificateArn, // REQUIRED
            ]);
        } catch (AcmException $e) {
            $error_message = "Certificate: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));

            $output =  [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } catch (AwsException $e) {
            $error_message = "Certificate: " . $CertificateArn . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));

            $output = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return $output ;
    } // request certificate


    public function certificate_exists($arn)
    {
        $output = false;

        try {
            $result = $this->acm_client->describeCertificate([
                'CertificateArn' => $arn
            ]);
            $output = true;
            return $output;
        } catch (AcmException $e) {
            $output = false;
        }

        return $output;
    } // certificate exists
}
