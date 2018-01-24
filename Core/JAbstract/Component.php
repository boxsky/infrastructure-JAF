<?php
namespace JAF\Core\JAbstract;

abstract class Component extends Page {
    final public function get_title() {
        return '';
    }

    final public function get_layout() {
        return '';
    }

    final function get_components() {
        return [];
    }
}