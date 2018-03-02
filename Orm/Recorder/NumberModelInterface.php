<?php
namespace JAF\Orm\Recorder;

interface NumberModelInterface {
    static function get_write_db_config();

    static function get_read_db_config();

    static function get_table_name();

    function replace();


}