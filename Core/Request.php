<?php
namespace JAF\Core;

class Request {
    protected static $instance;
    public static function get_instance() {
        if(!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return static::$instance;
    }

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

    protected $client_ip;
    public function get_client_ip() {
        if (!isset($this->client_ip)) {
            $ip = false;
            if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            }
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
                if ($ip) {
                    array_unshift($ips, $ip);
                    $ip = false;
                }
                for ($i = 0; $i < count($ips); $i++) {
                    if (!preg_match("/^(10|172\.16|192\.168)\./i", $ips[$i])) {
                        $ip = $ips[$i];
                        break;
                    }
                }
            }
            if (!$ip) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $this->client_ip = $ip;
        }
        return $this->client_ip;
    }

    public function get_user_agent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function get_cookie($name) {
        return $_COOKIE[$name] ?? null;
    }
}