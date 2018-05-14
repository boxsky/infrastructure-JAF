<?php
namespace JAF\Job;

class BuildResource extends ParentJob {
    private $page_dir;
    private $public_dir;
    private $dist_dir;
    private $source_dir;
    private $page_files;
    private $page_class_namespace_prefix;
    private $component_class_namespace_prefix;
    private $component_class_namespace_suffix;

    public function __construct() {
        $this->page_dir = APP_PATH . 'Page';
        $this->public_dir = ROOT_PATH . 'public';
        $this->source_dir = 'source';
        $this->dist_dir = $this->public_dir . DIRECTORY_SEPARATOR . 'tmp';
        $this->page_files = [];
        $this->page_class_namespace_prefix = 'App\Page';
        $this->component_class_namespace_prefix = 'App\Component';
        $this->component_class_namespace_suffix = 'Component';
    }

    public function handle_main($params) {
        //初始化dist目录
        $this->init_dist_dir();

        //取出所有page文件
        $this->read_all_page_dirs($this->page_dir);
        if (empty($this->page_files)) exit;

        //获取所有page类
        $page_classes = $this->build_page_classes($this->page_files);
        if (!$page_classes) exit;

        //build静态资源
        foreach ($page_classes as $page_class) {
            if (class_exists($page_class)) {
                $reflection_class = new \ReflectionClass($page_class);
                if ($reflection_class->isAbstract()) continue;
                $this->build_resource($page_class);
            }
        }
    }

    private function read_all_page_dirs($dir) {
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while ($file = readdir($handle)) {
                    if ($file == '.' || $file == '..') continue;
                    $file_dir = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($file_dir)) {
                        $this->read_all_page_dirs($file_dir);
                    } else if (preg_match('/Page.php$/', $file, $matches)) {
                        $this->page_files[] = $file_dir;
                    }
                }
                closedir($handle);
            }
        }
    }

    private function build_page_classes($page_files) {
        $page_classes = [];
        foreach ($page_files as $page_file) {
            $relative_file_path = str_replace($this->page_dir, '', $page_file);
            $relative_file_name = str_replace('.php', '', $relative_file_path);
            $page_classes[] = $this->page_class_namespace_prefix . str_replace(DIRECTORY_SEPARATOR, '\\', $relative_file_name);
        }
        return $page_classes;
    }

    private function build_resource($page_class_name) {
        //获取page资源
        list($compressed_styles, $compressed_scripts) = $this->get_page_resources($page_class_name);

        //合并后的文件名
        $compressed_file = strtolower(str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Page', '', str_replace('App\Page\\', '', $page_class_name))));
        $compressed_file_info_arr = explode(DIRECTORY_SEPARATOR, $compressed_file);
        $compressed_file_name = array_pop($compressed_file_info_arr);

        //重新组装资源数组
        $resource_arr = [];
        if ($compressed_styles) $resource_arr['css'] = $compressed_styles;
        if ($compressed_scripts) $resource_arr['js'] = $compressed_scripts;

        //生成文件
        foreach ($resource_arr as $resource_type => $resources) {
            //创建合并后的文件所在的文件夹
            $compressed_resource_dir = $this->dist_dir . DIRECTORY_SEPARATOR . $resource_type . DIRECTORY_SEPARATOR . str_replace($compressed_file_name, '', $compressed_file);
            if (!is_dir($compressed_resource_dir)) {
                $mkdir_resource_res = mkdir($compressed_resource_dir, 0777, true);
                if (!$mkdir_resource_res) {
                    echo "mkdir resource fail: {$compressed_resource_dir}\n";
                    exit;
                }
            }
            //创建合并后的文件
            $resource_file = $compressed_resource_dir . $compressed_file_name . '.' . $resource_type;
            $fp = fopen($resource_file, 'w');
            fclose($fp);
            //合并资源内容
            foreach ($resources as $resource) {
                $resource_file_path = $this->public_dir . DIRECTORY_SEPARATOR . $this->source_dir . DIRECTORY_SEPARATOR . $resource_type . DIRECTORY_SEPARATOR . $resource;
                if (!file_exists($resource_file_path)) continue;
                $content = file_get_contents($resource_file_path) . "\n";
                file_put_contents($resource_file, $content, FILE_APPEND);
            }
        }
    }

    private function init_dist_dir() {
        if (!is_dir($this->dist_dir)) {
            $mkdir_dist_res = mkdir($this->dist_dir, 0777, true);
            if (!$mkdir_dist_res) {
                echo "init dist dir fail: {$this->dist_dir}\n";
                exit;
            }
        } else {
            $clear_dist_dir_cmd = 'cd '.$this->dist_dir.' && rm -rf * && echo 1';
            $clear_rs = exec($clear_dist_dir_cmd);
            if ($clear_rs !== '1') {
                echo "init dist dir fail: {$this->dist_dir}\n";
                exit;
            }
        }
    }

    private function get_page_resources($page_class_name) {
        $page = new $page_class_name();
        $page_compressed_styles = $page->get_compressed_styles();
        $page_compressed_scripts = $page->get_compressed_javascripts();
        $page_components = $page->get_components();
        list($components_compressed_styles, $components_compressed_scripts) = $this->get_components_resources($page_components);
        $page_compressed_styles = array_unique(array_merge($components_compressed_styles, $page_compressed_styles));
        $page_compressed_scripts = array_unique(array_merge($components_compressed_scripts, $page_compressed_scripts));
        return [$page_compressed_styles, $page_compressed_scripts];
    }

    private function get_components_resources($page_components) {
        $components_compressed_styles = $components_compressed_scripts = [];
        foreach ($page_components as $page_component) {
            list($component_compressed_styles, $component_compressed_scripts) = $this->get_component_resources($page_component);
            $components_compressed_styles = array_merge($components_compressed_styles, $component_compressed_styles);
            $components_compressed_scripts = array_merge($components_compressed_scripts, $component_compressed_scripts);
        }
        return [$components_compressed_styles, $components_compressed_scripts];
    }

    private function get_component_resources($page_component) {
        $page_component_class_name = $this->component_class_namespace_prefix . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $page_component).$this->component_class_namespace_suffix;
        $component = new $page_component_class_name();
        return [$component->get_compressed_styles(), $component->get_compressed_javascripts()];
    }
}
