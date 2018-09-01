<?php
namespace JAF\Orm\V2\Demo;

use JAF\Orm\V2\ShardingModel;

class Sharding extends ShardingModel  {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_sharding_';
    }

    public static function get_table_pk() {
        return 'id';
    }

    public function table_suffix_route($params) {
        $mod_num = 2;
        $res = $params['id'] % $mod_num;
        return $res == 0 ? $mod_num : $res;
    }
}