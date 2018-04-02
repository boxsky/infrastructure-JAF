<?php
namespace JAF\Auth;

use JAF\Core\Util\Encrypt;

class Auth {
    public static function build_cookie_user_id_key($key, $ip, $ua, $salt) {
        return substr(md5($key.$ip.$salt.$ua), 0, 8);
    }

    public static function build_cookie_user_id_value($user_id, $secret) {
        return Encrypt::encrypt_aes($user_id, $secret);
    }

    public static function restore_cookie_user_id_value($encrypted_user_id, $secret) {
        return Encrypt::decrypt_aes($encrypted_user_id, $secret);
    }
}