<?php
namespace JAF\Orm;

interface PKDistributorInterface {
    static function get_write_db_config();

    static function get_read_db_config();

    static function get_table_name();

    static function get_table_pk();

    static function enable_orm_pk_cache();

    static function distribute();

    static function stub();
}