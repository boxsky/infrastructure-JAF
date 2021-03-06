<?php
namespace JAF\Cache;

use JAF\Exception\FrameException;

class Redis extends \Redis {
    private $timeout = 2;
    private $key_prefix;

    const ENUM_CONNECT_MODE_REQUEST = 1;
    const ENUM_CONNECT_MODE_PROCESS = 2;

    public function __construct($name, $connect_mode=self::ENUM_CONNECT_MODE_REQUEST, $select_db=true) {
        parent::__construct();
        $this->key_prefix = 'JAF:'.APP_NAME.':';
        $redis_config = jconfig($name, 'redis');
        if (empty($redis_config['host']) || empty($redis_config['port'])) {
            throw new FrameException(FrameException::ENUM_REDIS_CONFIG_ERR);
        }
        $timeout = !empty($redis_config['timeout']) ? $redis_config['timeout'] : $this->timeout;
        switch ($connect_mode) {
            case self::ENUM_CONNECT_MODE_PROCESS:
                $connect_function = 'pconnect';
                break;
            default:
                $connect_function = 'connect';
                break;
        }
        $conn_res = $this->$connect_function($redis_config['host'], $redis_config['port'], $timeout);
        if (!$conn_res) {
            throw new FrameException(FrameException::ENUM_REDIS_CONNECT_ERR);
        }
        if (!empty($redis_config['password'])) {
            $auth_res = $this->auth($redis_config['password']);
            if (!$auth_res) {
                throw new FrameException(FrameException::ENUM_REDIS_AUTH_FAIL);
            }
        }
        if ($select_db) {
            $database = isset($redis_config['database']) ? intval($redis_config['database']) : 0;
            $this->select($database);
        }
    }

    public function set($key, $value, $timeout=null) {
        $timeout = !empty($timeout) ? $timeout : $this->timeout;
        return parent::set($this->rebuild_key($key), $this->rebuild_value($value), $timeout);
    }

    public function setex($key, $ttl, $value) {
        return parent::setex($this->rebuild_key($key), $ttl, $this->rebuild_value($value));
    }

    public function get($key) {
        return $this->format_value(parent::get($this->rebuild_key($key)));
    }

    public function del($key) {
        return parent::del($this->rebuild_key($key));
    }

    public function incr($key) {
        return parent::incr($this->rebuild_key($key));
    }

    public function decr($key) {
        return parent::decr($this->rebuild_key($key));
    }

    public function setTime($key, $timeout) {
        return parent::expire($this->rebuild_key($key), $timeout);
    }

    public function getTime($key) {
        return parent::ttl($this->rebuild_key($key));
    }

    public function flushDB() {
        return parent::flushDB();
    }

    private function rebuild_key($key) {
        return $this->key_prefix.$key;
    }

    private function rebuild_value($value) {
        return is_numeric($value) ? $value : serialize($value);
    }

    private function format_value($value) {
        return is_numeric($value) ? $value : unserialize($value);
    }
}