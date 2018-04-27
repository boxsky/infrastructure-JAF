<?php
namespace JAF\Core;

use JAF\Cache\LocalSharedMemory;
use JAF\Cache\RedisClient;
use JAF\Exception\FrameException;

class BaseBll {
    protected static $instance;
    protected $lsm_switch = true;
    protected $cache_switch = true;  //缓存开关

    protected function __construct() {}

    /**
     * 单例
     */
    public static function get_instance() {
        if(!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 使用本地共享内存调用方法
     */
    public function use_func_with_lsm($func_name, $params, $ttl, $use_secondary_cache=false) {
        if (!method_exists($this, $func_name)) throw new FrameException(FrameException::ENUM_BLL_NOT_EXIST, $func_name);
        if ($this->lsm_switch) {
            try {
                $lsm = LocalSharedMemory::get_instance();
            } catch (\Exception $e) {
                $lsm = null;
            }
            $cache_key = 'Bll_Cache::'.get_called_class().'::'.$func_name.'::'.json_encode($params);
            $res = !is_null($lsm) ? $lsm->get($cache_key) : false;
            if ($res === false) {
                if ($use_secondary_cache) {
                    $res = $this->use_func_with_cache($func_name, $params, $ttl);
                } else {
                    $res = call_user_func_array([$this, $func_name], $params);
                }
                if (!is_null($lsm)) {
                    $lsm->set($cache_key, $res, $ttl);
                }
            }
        } else {
            $res = call_user_func_array([$this, $func_name], $params);
        }

        return $res;
    }

    /**
     * 使用缓存调用方法
     */
    public function use_func_with_cache($func_name, $params, $ttl) {
        if (!method_exists($this, $func_name)) throw new FrameException(FrameException::ENUM_BLL_NOT_EXIST, $func_name);
        if ($this->cache_switch) {
            try {
                $redis_client = RedisClient::get_instance()->get_redis_client('default');
            } catch (\Exception $e) {
                $redis_client = null;
            }
            $cache_key = 'Bll_Cache::'.get_called_class().'::'.$func_name.'::'.json_encode($params);
            $res = !is_null($redis_client) ? $redis_client->get($cache_key) : false;
            if ($res === false) {
                $res = call_user_func_array([$this, $func_name], $params);
                if (!is_null($redis_client)) {
                    $redis_client->setex($cache_key, $ttl, $res);
                }
            }
        } else {
            $res = call_user_func_array([$this, $func_name], $params);
        }
        return $res;
    }
}