<?php
namespace JAF\Core;

final class Middleware {
    private static $instance;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new Middleware();
        }
        return self::$instance;
    }

    public function get_middlewares($controller) {
        if (is_yac_enable()) {
            $localSharedMemory_client = \JAF\Cache\LocalSharedMemory::get_instance();
            $middleware_key = 'jmiddleware::' . $controller;
            $all_middlewares = $localSharedMemory_client->get($middleware_key);
            if ($all_middlewares === false) {
                $all_middlewares = $this->load_middlewares($controller);
                $ttl = (!defined('ENV') || ENV == 'dev') ? 5 : 86400;
                $localSharedMemory_client->set($middleware_key, $all_middlewares, $ttl);
            }
        } else {
            $all_middlewares = $this->load_middlewares($controller);
        }
        return $all_middlewares;
    }

    private function load_middlewares($controller) {
        $all_middlewares = [];
        try{
            /**
             * 全局中间件
             */
            $middlewars = jconfig(null, 'middleware');
            if (!empty($middlewars['global'])) {
                foreach ($middlewars['global'] as $value) {
                    $all_middlewares[$value] = $value;
                }
            }
            /**
             * 控制器定制的中间件
             */
            $controller_middlewares = !empty($middlewars['custom'][$controller]) ? $middlewars['custom'][$controller] : [];
            /**
             * 取不到定制的中间件,则取默认中间件
             */
            if (empty($controller_middlewares)) $controller_middlewares = !empty($middlewars['default']) ? $middlewars['default'] : [];
            /**
             * 若 `!` 开头,则表示非,即不使用该中间件
             */
            if (!empty($controller_middlewares)) {
                foreach ($controller_middlewares as $value) {
                    if (strpos($value, '!') === 0) {
                        $value = substr($value, 1);
                        unset($all_middlewares[$value]);
                    } else {
                        $all_middlewares[$value] = $value;
                    }
                }
            }
            $all_middlewares = array_values(array_unique($all_middlewares));
            $all_middlewares = array_map(function($middleware){
                return 'App\Middleware\\'.$middleware;
            }, $all_middlewares);
        } catch (\Exception $e) {}
        return $all_middlewares;
    }
}