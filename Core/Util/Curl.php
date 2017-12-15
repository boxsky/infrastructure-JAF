<?php
namespace JAF\Core\Util;

class Curl {

    private $url;
    private $data;
    private $method;
    private $header = null;
    private $connecttimeout;
    private $timeout;

    const ENUM_METHOD_GET = 'get';
    const ENUM_METHOD_POST = 'post';

    public function __construct() {
        $this->init();
    }

    public function init() {
        $this->set_method(self::ENUM_METHOD_GET);
        $this->set_connecttimeout(5000);
        $this->set_timeout(6000);
    }

    public function set_url($url) {
        $this->url = $url;
    }

    public function get_url() {
        return $this->url;
    }

    public function set_data($data, $type='form') {
        if ($type == 'json') {
            $data = json_encode($data);
        } else if ($type == 'key-value') {
            $data_str = '';
            foreach ($data as $key => $value) {
                $data_str .= $key.'='.$value.'&';
            }
            $data = rtrim($data_str, '&');
        }
        $this->data = $data;
    }

    public function get_data() {
        return $this->data;
    }

    public function set_method($method) {
        $this->method = $method;
    }

    public function get_method() {
        return $this->method;
    }

    public function set_header($header) {
        $this->header = $header;
    }

    public function get_header() {
        return $this->header;
    }

    public function set_connecttimeout($connexcttimeout) {
        $this->connecttimeout = $connexcttimeout;
    }

    public function get_connecttimeout() {
        return $this->connecttimeout;
    }

    public function set_timeout($timeout) {
        $this->timeout = $timeout;
    }

    public function get_timeout() {
        return $this->timeout;
    }

    public function execute() {
        $curl = curl_init();
        $data = $this->get_data();
        $url = $this->get_url();
        if (strtolower($this->get_method()) == self::ENUM_METHOD_GET) {
            $param_str = '';
            foreach ($data as $f => $v) {
                $param_str .= $f.'='.$v.'&';
            }
            $param_str = rtrim($param_str, '&');
            if ($param_str) {
                $url .= '?'.$param_str;
            }
        } else {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        $header = $this->get_header();
        if (!is_null($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->get_header());
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $this->get_connecttimeout());
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, $this->get_timeout());

        $this->response_text = curl_exec($curl);

        $this->curl_info = curl_getinfo($curl);
        if ($this->curl_info['http_code'] == 200) {
            curl_close($curl);
            return $this->response_text;
        } else {
            $this->curl_errno = curl_errno($curl);
            $this->curl_error = curl_error($curl);
            curl_close($curl);
            throw new \Exception($this->curl_error, $this->curl_errno);
        }
    }

    private $response_text;

    private $curl_info;

    private $curl_errno;

    private $curl_error;

    public function get_response_text() {
        return $this->response_text;
    }

    public function get_curl_info() {
        return $this->curl_info;
    }

    public function get_curl_errno() {
        return $this->curl_errno;
    }

    public function get_curl_error() {
        return $this->curl_error;
    }
}