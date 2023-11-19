<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Curl_library
{
    private $http_codes_ok;
    protected $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->http_codes_ok = [200, 201, 204];
    }

    public function simple_get($url, $data = [], $options = [])
    {
        if (count($data) > 0) {
            $url .= '?' . http_build_query($data, '', '&');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        if (ENVIRONMENT == "development") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $dataset = [];
        $dataset['url'] = $url;
        $dataset['http_response'] = curl_exec($ch);
        $dataset['http_response_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dataset['successful'] = in_array((int) $dataset['http_response_code'], $this->http_codes_ok) ? true : false;
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);


        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($dataset['http_response'], 0, $header_size);
        $dataset['http_response'] = substr($dataset['http_response'], $header_size);

        log_message('debug', '[curl_library] url=' . $url . ' at ' . __METHOD__ . ':' . __LINE__);
        log_message('debug', '[curl_library] request_headers=' . json_encode($headerSent) . ' at ' . __METHOD__ . ':' . __LINE__);
        log_message('debug', '[curl_library] response header=' . json_encode($header) . ' at ' . __METHOD__ . ':' . __LINE__);

        curl_close($ch);
        return $dataset;
    }

    public function simple_put($url, $data, $options = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        if (ENVIRONMENT == "development") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }


        $dataset = [];
        $dataset['url'] = $url;
        $dataset['http_response'] = curl_exec($ch);
        $dataset['http_response_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dataset['successful'] = in_array((int) $dataset['http_response_code'], $this->http_codes_ok) ? true : false;

        curl_close($ch);
        return $dataset;
    }

    public function simple_post($url, $data, $options = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        if (ENVIRONMENT == "development") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        log_message('debug', '[curl_library] url=' . $url . ' at ' . __METHOD__ . ':' . __LINE__);

        $dataset = [];
        $dataset['url'] = $url;
        $dataset['http_response'] = curl_exec($ch);
        $dataset['http_response_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dataset['successful'] = in_array((int) $dataset['http_response_code'], $this->http_codes_ok) ? true : false;
        $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        log_message('debug', '[curl_library] request_headers=' . json_encode($headerSent) . ' at ' . __METHOD__ . ':' . __LINE__);
        log_message('debug', '[curl_library] raw http_response=' . json_encode($dataset['http_response']) . ' at ' . __METHOD__ . ':' . __LINE__);

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($dataset['http_response'], 0, $header_size);
        $dataset['http_response'] = substr($dataset['http_response'], $header_size);


        log_message('debug', '[curl_library] response header=' . json_encode($header) . ' at ' . __METHOD__ . ':' . __LINE__);

        curl_close($ch);
        return $dataset;
    }
    
    public function simple_delete($url, $data, $options = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        if (ENVIRONMENT == "development") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }


        $dataset = [];
        $dataset['url'] = $url;
        $dataset['http_response'] = curl_exec($ch);
        $dataset['http_response_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $dataset['successful'] = in_array((int) $dataset['http_response_code'], $this->http_codes_ok) ? true : false;

        curl_close($ch);
        return $dataset;
    }
}
