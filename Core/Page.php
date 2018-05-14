<?php
namespace JAF\Core;

class Page {
    private $page_name;
    private $data;
    private $type;
    private $title;
    private $page_class;
    private $cdn_prefix;
    private $default_version;

    public function __construct($page_name, $data, $type='Page') {
        $this->set_page_name($page_name);
        $this->set_data($data);
        $this->set_type($type);
        $this->set_cdn_prefix();
        $this->set_default_version();
    }

    public function render() {
        /**
         * 视图类
         */
        $page_class_name = 'App\\'.$this->get_type().'\\'.$this->get_page_name().$this->get_type();
        $page_class = new $page_class_name();

        /**
         * 传值给Page
         */
        if ($this->get_data()) {
            foreach ($this->get_data() as $k => $v) {
                $page_class->set_assigned_data($k, $v);
            }
        }
        $this->set_page_class($page_class);

        /**
         * 加载视图布局
         */
        $layout_template = $this->get_page_class()->get_layout();
        if ($layout_template) {
            /**
             * 设置页面标题
             */
            $this->set_title($this->get_page_class()->get_title());
            require_once ROOT_PATH.'resource/layout/'.$layout_template.'.phtml';
        } else {
            $this->real_page($this->get_type());
        }
    }

    public function real_page($type='Page') {
        $real_page = $this->get_page_class()->get_view();
        /**
         * format Page path
         */
        $real_page_arr = explode('/', $real_page);
        $real_page_name = array_pop($real_page_arr);
        $real_page_arr = array_map(function($t){return strtolower($t);}, $real_page_arr);
        $real_page_arr[] = $real_page_name;
        $real_page = implode('/', $real_page_arr);
        /**
         * 设置视图数据
         */
        $page_data = $this->get_page_class()->get_assigned_data();
        if ($page_data) {
            foreach ($page_data as $k => $v) {
                ${$k} = $v;
            }
        }
        include ROOT_PATH.'resource/'.lcfirst($type).'/'.$real_page.'.phtml';
    }

    private function set_page_name($page_name) {
        $this->page_name = $page_name;
    }

    private function get_page_name() {
        return $this->page_name;
    }

    private function set_data($data) {
        $this->data = $data;
    }

    private function get_data() {
        return $this->data;
    }

    private function set_type($type) {
        $this->type = $type;
    }

    private function get_type() {
        return $this->type;
    }

    private function set_page_class($page_class) {
        $this->page_class = $page_class;
    }

    private function get_page_class() {
        return $this->page_class;
    }

    public function get_title() {
        return $this->title;
    }

    private function set_title($title) {
        $this->title = $title;
    }

    public function get_styles() {
        $resources = $this->get_page_class()->get_styles();
        return $this->get_single_resource_uris($resources);
    }

    public function get_javascripts() {
        $resources = $this->get_page_class()->get_javascripts();
        return $this->get_single_resource_uris($resources);
    }

    private function get_compressed_resources($ext) {
        switch ($ext) {
            case 'css':
                $function = 'get_compressed_styles';
                break;
            case 'js':
                $function = 'get_compressed_javascripts';
                break;
            default:
                $function = null;
                break;
        }
        $res = [];
        if (!is_null($function)) {
            $page = $this->get_page_class();
            $resources = [];
            $components = $page->get_components();
            if ($components) {
                foreach ($components as $component) {
                    $component_class_name = 'App\Component\\'.$component.'Component';
                    $compoent_class = new $component_class_name();
                    $component_resources = call_user_func([$compoent_class, $function]);
                    $resources = array_merge($resources, $component_resources);
                }
            }
            $page_resources = call_user_func([$page, $function]);
            $resources = array_merge($resources, $page_resources);

            $res = $this->get_compressed_resource_uris($resources, '.'.$ext);
        }
        return $res;
    }

    public function get_compressed_styles() {
        return $this->get_compressed_resources('css');
    }

    public function get_compressed_javascripts() {
        return $this->get_compressed_resources('js');
    }

    public function component($component_name, $data) {
        $page = new Page($component_name, $data, $type='Component');
        $page->render();
    }

    private function get_single_resource_uris($resources) {
        return array_map(function($resource) {
            if (preg_match('/:\/\//', $resource) || preg_match("#^//#", $resource)) {
                return $resource;
            } else {
                return $this->get_cdn_prefix().'dist/'.$this->build_uri($resource)."?v={$this->get_default_version()}";
            }
        }, $resources);
    }

    private function get_compressed_resource_uris($resources, $ext) {
        $type = str_replace('.', '', $ext);
        if (ENV == 'production') {
            return [$this->get_cdn_prefix().$this->build_uri($this->transform_page_name_to_file($this->get_page_name(), $ext, $type), 'c')."?v={$this->get_default_version()}"];
        } else {
            return array_map(function($resource) use($type) {
                return $this->get_cdn_prefix().'source/'.$type.'/'.$this->build_uri($resource);
            }, $resources);
        }
    }

    private function transform_page_name_to_file($page_name, $ext, $type) {
        return 'build/'.$type.'/'.strtolower(str_replace('\\', '/', $page_name)).$ext;
    }

    private function build_uri($resource) {
        $uri = $resource;
        if (defined('RELEASE_VERSION') && RELEASE_VERSION) {
            $uri .= '?v='.RELEASE_VERSION;
        }
        return $uri;
    }

    private function set_cdn_prefix() {
        $request = APP::get_instance()->get_request();
        $schema = (is_callable(array($request, 'is_secure')) && $request->is_secure()) ? 'https://' : 'http://';
        $host = jconfig("cdn_host", "resource");
        $path = jconfig("cdn_path", "resource");
        $this->cdn_prefix = $schema.$host.$path;
    }

    private function get_cdn_prefix() {
        return $this->cdn_prefix;
    }

    private function set_default_version() {
        $this->default_version = defined('VERSION') ? VERSION : time();
    }

    private function get_default_version() {
        return $this->default_version;
    }
}