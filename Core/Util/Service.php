<?php
namespace JAF\Core\Util;

use JAF\Exception\FrameException;

class Service {
    public static function call($service_name, $api_name, $data, $header=array(), $timeout=null) {
        //service config
        $service_config = jconfig($service_name, 'service');
        self::validate_service_config($service_config, $api_name);
        $domain = $service_config['domain'];
        $api_config = $service_config['apis'][$api_name];

        //url
        $uri = $api_config['url'] ?? '/'.str_replace(':', '/', $api_name);
        $url = 'http://'.$domain.$uri;

        //method
        $method = $api_config['method'];

        //curl
        $curl_client = new Curl();
        if ($header) {
            $curl_client->set_header($header);
        }
        $curl_client->set_method($method);
        $curl_client->set_data($data);
        $curl_client->set_url($url);
        if (!is_null($timeout)) {
            $curl_client->set_timeout($timeout);
        }

        try{
            $res = $curl_client->execute();
        } catch (\Exception $e) {
            $res = false;
        }
        return $res;
    }

    private static function validate_service_config($service_config, $api_name) {
        if (!isset($service_config['domain']) || !isset($service_config['apis'])) {
            throw new FrameException(FrameException::ENUM_SERVICE_CONFIG_ERR);
        }
        if (!isset($service_config['apis'][$api_name])) {
            throw new FrameException(FrameException::ENUM_SERVICE_API_NOT_EXIST);
        }
    }
}