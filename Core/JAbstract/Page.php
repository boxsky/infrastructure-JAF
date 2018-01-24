<?php
namespace JAF\Core\JAbstract;

use JAF\Core\JInterface\Page as PageInterface;

abstract class Page implements PageInterface {
    private $assigned_data = [];

    public function get_styles() {
        return [];
    }

    public function get_compressed_styles() {
        return [];
    }

    public function get_javascripts() {
        return [];
    }

    public function get_compressed_javascripts() {
        return [];
    }

    public function get_components() {
        return [];
    }

    final function set_assigned_data($k, $v) {
        $this->assigned_data[$k] = $v;
    }

    public function get_assigned_data() {
        return $this->assigned_data;
    }
}