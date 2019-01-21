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
            $redis_config = jconfig($name, 'redis');
            $this->redis_list[$name] = [
                'instance' => $this->load_redis($name),
                'database' => isset($redis_config['database']) ? intval($redis_config['database']) : 0
            ];
        }
        $redis_instance = $this->redis_list[$name]['instance'];
        $redis_instance->select($this->redis_list[$name]['database']);
        return $redis_instance;
    }

    private function load_redis($name) {
        $redis = new Redis($name, Redis::ENUM_CONNECT_MODE_PROCESS);
        return $redis;
    }
}