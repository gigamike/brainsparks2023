<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Googleservices_library
{
    protected $CI;
    private $key;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->key = $this->CI->config->item('mm8_google_api_id');
    }

    public function get_geocode($address)
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $this->key;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $curl_response = curl_exec($curl);
        $curl_response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $json_obj = json_decode($curl_response);

        $geocode = [];
        $geocode['lat'] = isset($json_obj->{'results'}[0]->{'geometry'}->{'location'}->{'lat'}) ? $json_obj->{'results'}[0]->{'geometry'}->{'location'}->{'lat'} : "";
        $geocode['lng'] = isset($json_obj->{'results'}[0]->{'geometry'}->{'location'}->{'lng'}) ? $json_obj->{'results'}[0]->{'geometry'}->{'location'}->{'lng'} : "";

        return $geocode;
    }
}
