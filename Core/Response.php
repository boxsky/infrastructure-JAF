<?php
namespace JAF\Core;

class Response {
    protected static $instance;
    public static function get_instance() {
        if(!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return static::$instance;
    }

    public function set_cookie($name, $value, $expire=0, $path=null, $domain=null, $secure=false, $httponly=false) {
        return setcookie($name, $value, $expire ? time() + intval($expire) : 0, $path, $domain, $secure, $httponly);
    }

    public function remove_cookie($name, $path=null, $domain=null, $secure=false, $httponly=false) {
        return $this->set_cookie($name, null, -3600, $path, $domain, $secure, $httponly);
    }

    public function redirect($url, $permanent=false, $is_exit=true) {
        header("Location: $url", true, $permanent ? 301 : 302);
        if ($is_exit) exit(0);
    }
}