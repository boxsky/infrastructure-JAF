<?php
namespace JAF\Orm\Demo;

use JAF\Orm\Model;

class TestWrite extends Model {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_write';
    }

    public static function get_table_pk() {
        return 'id';
    }
}