<?php

defined('BASEPATH') or exit('No direct script access allowed');

//load sdk
require_once APPPATH . 'third_party/google_authenticator/GoogleAuthenticator.php';

class Google_authenticator_library
{
    protected $CI;
    protected $GA;

    public function __construct()
    {
        $this->CI = & get_instance();

        $this->GA = new PHPGangsta_GoogleAuthenticator();
    }

    public function getQRCode($name, $secret, $title)
    {
        $qrCodeUrl = $this->GA->getQRCodeGoogleUrl($name, $secret, $title);
        return $qrCodeUrl;
    }

    public function checkCode($secret, $code)
    {
        return $this->GA->verifyCode($secret, $code, 2); // 2 = 2*30sec clock tolerance
    }

    public function getCode($secret)
    {
        return $this->GA->getCode($secret);
    }
    
    public function getSecret()
    {
        return $this->GA->createSecret();
    }

    public function index()
    {
        $secret = $this->GA->createSecret();
        echo "Secret is: ".$secret."\n\n";

        $qrCodeUrl = $this->GA->getQRCodeGoogleUrl('CRM', $secret);
        echo "Google Charts URL for the QR-Code: ".$qrCodeUrl."\n\n";

        $oneCode = $this->GA->getCode($secret);
        echo "Checking Code '$oneCode' and Secret '$secret':\n";

        $checkResult = $this->GA->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
        if ($checkResult) {
            echo 'OK';
        } else {
            echo 'FAILED';
        }
    }
}
