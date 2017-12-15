<?php
namespace JAF\Cache;

class RedisClient extends \Redis {
    private $redis_list;

    private static $instance;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new RedisClient();
        }
        return self::$instance;
    }

    public function get_redis_client($name) {
        if (!isset($this->redis_list[$name])) {
            $this->redis_list[$name] = $this->load_redis($name);
        }
        return $this->redis_list[$name];
    }

    private function load_redis($name) {
        $redis = new Redis($name, Redis::ENUM_CONNECT_MODE_PROCESS);
        return $redis;
    }
}