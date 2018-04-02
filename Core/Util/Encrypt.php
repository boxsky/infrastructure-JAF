<?php
namespace JAF\Core\Util;

class Encrypt {
    public static function encrypt_aes($str, $secret) {
        return base64_encode(openssl_encrypt($str, 'AES-128-ECB', $secret, OPENSSL_RAW_DATA));
    }

    public static function decrypt_aes($str, $secret) {
        return openssl_decrypt(base64_decode($str), 'AES-128-ECB', $secret, OPENSSL_RAW_DATA);
    }
}