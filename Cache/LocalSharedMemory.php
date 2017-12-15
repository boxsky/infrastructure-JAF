<?php
namespace JAF\Cache;

use JAF\Exception\FrameException;

class LocalSharedMemory {
    private static $instance;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new LocalSharedMemory();
        }
        return self::$instance;
    }

    private $yac_client;
    private $key_prefix;

    function __construct() {
        if (!is_yac_enable()) {
            throw new FrameException(FrameException::ENUM_USE_YAC_BUT_YAC_DISABLE);
        }
        $this->yac_client = new \Yac();
        $this->key_prefix = 'JAF::'.APP_NAME.'::';
    }

    public function set($key, $value, $ttl) {
        $this->yac_client->set($this->rebuild_key($key), $value, $ttl);
    }

    public function get($key) {
        return $this->yac_client->get($this->rebuild_key($key));
    }

    public function delete($key) {
        $this->yac_client->delete($this->rebuild_key($key));
    }

    public function flush() {
        $this->yac_client->flush();
    }

    private function rebuild_key($key) {
        return md5($this->key_prefix.$key);
    }
}