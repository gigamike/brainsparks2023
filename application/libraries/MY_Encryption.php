<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Encryption extends CI_Encryption
{
    public function __construct()
    {
        parent::__construct();
    }

    public function url_encrypt($string)
    {
        $ciphertext = $this->encrypt($string);
        return $ciphertext ? str_replace(['+', '/', '='], ['-', '_', '~'], $ciphertext) : false;
    }

    public function url_decrypt($ciphertext)
    {
        $ciphertext = str_replace(['-', '_', '~'], ['+', '/', '='], $ciphertext);
        return $this->decrypt($ciphertext);
    }
}
