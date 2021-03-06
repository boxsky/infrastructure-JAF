<?php
namespace JAF\Orm\Demo;

use JAF\Orm\ShardingModel;

class TestWriteSharding extends ShardingModel  {
    const MOD_NUM = 2;

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
        $mod_num = self::MOD_NUM;
        $res = $params['id'] % $mod_num;
        return $res == 0 ? $mod_num : $res;
    }
}