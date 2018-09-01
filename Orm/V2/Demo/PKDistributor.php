<?php
namespace JAF\Orm\V2\Demo;

use JAF\Orm\V2\PKDistributorModel;

class PKDistributor extends PKDistributorModel {
    public static function get_write_db_config() {
        return 'xhj_dev';
    }

    public static function get_read_db_config() {
        return 'xhj_dev';
    }

    public static function get_table_name() {
        return 't_orm_pk_distributor';
    }

    public static function get_table_pk() {
        return 'id';
    }

    public static function stub() {
        return ['stub' => 'a'];
    }
}