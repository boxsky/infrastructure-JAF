<?php
namespace JAF\Auth;

use JAF\Core\Util\Curl;

class Oauth {
    private static $instance;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new Oauth();
        }
        return self::$instance;
    }

    public function get_token_by_code($code, $app_id, $secret, $get_token_url) {
        $data = [
            'app_id' => $app_id,
            'code' => $code,
            'secret' => $secret
        ];
        $curl = new Curl();
        $curl->set_url($get_token_url);
        $curl->set_method(Curl::ENUM_METHOD_POST);
        $curl->set_data($data);
        $curl->execute();
        $res = $curl->get_response_text();
        $res = json_decode($res, true);
        return (isset($res['code']) && $res['code']==200 && !empty($res['data']['token'])) ? $res['data']['token'] : null;
    }

    public function get_user_by_token($token, $get_user_url) {
        $curl = new Curl();
        $curl->set_url($get_user_url);
        $curl->set_method(Curl::ENUM_METHOD_GET);
        $curl->set_data(['token' => $token]);
        $curl->execute();
        $res = $curl->get_response_text();
        $res = json_decode($res, true);
        return (isset($res['code']) && $res['code']==200 && !empty($res['data'])) ? $res['data'] : null;
    }
}