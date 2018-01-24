<?php
namespace JAF\Core\JInterface;

interface Page {
    function get_layout();

    function get_title();

    function get_view();

    function get_styles();

    function get_compressed_styles();

    function get_javascripts();

    function get_compressed_javascripts();

    function get_components();

    function get_assigned_data();
}