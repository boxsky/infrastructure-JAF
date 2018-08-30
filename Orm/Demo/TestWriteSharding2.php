<?php
namespace JAF\Orm\Demo;

use JAF\Orm\ShardingModel;

class TestWriteSharding2 extends ShardingModel  {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_write_sharding_';
    }

    public static function get_table_pk() {
        return 'id';
    }

    public function table_suffix_route($params) {
        return '';
    }
}