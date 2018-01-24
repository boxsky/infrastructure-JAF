<?php
namespace JAF\Core;

use JAF\Core\JInterface\Page;

abstract class Component implements Page {
    private $assigned_data = [];

    final public function get_title() {
        return '';
    }

    final public function get_layout() {
        return '';
    }

    abstract function get_view();

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

    final function get_components() {
        return [];
    }

    final function set_assigned_data($k, $v) {
        $this->assigned_data[$k] = $v;
    }

    public function get_assigned_data() {
        return $this->assigned_data;
    }
}