<?php
namespace JAF\Core;

class Request {
    public function is_secure() {
        $is_secure_https = isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) === 'on';
        $is_secure_http_x_forwarded_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        return $is_secure_https || $is_secure_http_x_forwarded_proto;
    }

    public function is_post_method() {
        return ($this->get_method() == 'POST');
    }

    private function get_method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    protected $parameters;
    public function get_parameters() {
        if (!isset($this->parameters)) {
            $this->parameters = array_merge($_GET, $_POST);
        }
        return $this->parameters;
    }
}