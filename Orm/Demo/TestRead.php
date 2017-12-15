<?php
namespace JAF\Orm\Demo;

use JAF\Orm\Model;

class TestRead extends Model {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_read';
    }

    public static function get_table_pk() {
        return 'id';
    }
}