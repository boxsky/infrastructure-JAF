<?php
namespace JAF\Orm\V2\Demo;

use JAF\Orm\V2\Model;

class Normal extends Model {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_normal';
    }

    public static function get_table_pk() {
        return 'id';
    }
}