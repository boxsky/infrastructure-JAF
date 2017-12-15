<?php
namespace JAF\Core;

final class Router {
    private static $instance;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    public function mapping() {
        $uri = $this->get_uri();
        $mappings_static = jconfig('static', 'route');
        $res = jconfig('404Page', 'route');
        if (isset($mappings_static[$uri])) {
            $res = $mappings_static[$uri];
        } else {
            $mappings_dynamic = jconfig('dynamic', 'route');
            if ($mappings_dynamic) {
                foreach ($mappings_dynamic as $uri_preg => $controller_class) {
                    if (preg_match('@'.$uri_preg.'@', $uri_preg, $matches)) {
                        $res = $controller_class;
                    }
                }
            }
        }
        $res = 'App\Controller\\'.$res;
        return $res;
    }

    private function get_uri() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = (BASE_URI != '' && strpos($uri, BASE_URI.'/') === 0) ? substr($uri, strlen(BASE_URI)) : $uri;
        $pos = strpos($uri, '?');
        $uri = ($pos !== false) ? substr($uri, 0, $pos) : $uri;
        $uri = $uri ?? '/';
        return $uri;
    }
}