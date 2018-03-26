<?php
namespace JAF\Core;

use \JAF\Core\JInterface\Middleware as MiddlewareInterface;

final class APP {
    private static $instance;
    private $request;
    private $response;
    private $shutdown_functions = [];

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new APP();
        }
        return self::$instance;
    }

    private function __construct() {
        error_reporting(0);
        /**
         * 非致命错误处理
         */
        set_error_handler('jaf_warning_handler');

        /**
         * 致命错误处理
         */
        $this->register_shutdown_function('jaf_fatal_handler');
        register_shutdown_function([$this, 'shutdown']);

        $request = new Request();
        $this->set_request($request);

        $response = new Response();
        $this->set_response($response);

        $router = new Router();
        $this->set_router($router);
    }

    public function run() {
        /**
         * 路由
         */
        $controller_class = $this->get_router()->mapping();

        /**
         * 中间件
         */
        $middlewares = Middleware::get_instance()->get_middlewares($controller_class);
        if ($middlewares) {
            foreach ($middlewares as $middleware_class) {
                $middleware = new $middleware_class();
                $step = $middleware->handle();
                if ($step == MiddlewareInterface::STEP_BREAK) break;
                elseif ($step == MiddlewareInterface::STEP_EXIT) exit;
            }
        }

        /**
         * 控制器
         */
        $controller = new $controller_class($this->get_request());
        $res = $controller->handle();

        /**
         * 视图
         */
        if (is_array($res) && count($res)==2) {
            list($page, $data) = $res;
            if (is_null($page)) exit;
            (new Page($page, $data))->render();
        }
    }

    public function set_router($router) {
        $this->router = $router;
    }

    private function get_router() {
        return $this->router;
    }

    public function set_request($request) {
        $this->request = $request;
    }

    public function get_request() {
        return $this->request;
    }

    public function set_response($response) {
        $this->response = $response;
    }

    public function get_response() {
        return $this->response;
    }

    private function register_shutdown_function($function) {
        $this->shutdown_functions[] = $function;
    }

    public function shutdown() {
        if ($this->shutdown_functions) {
            foreach ($this->shutdown_functions as $shutdown_function) {
                call_user_func($shutdown_function);
            }
        }
    }
}