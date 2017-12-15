<?php
namespace JAF\Orm;

interface ModelInterface {
    static function get_write_db_config();

    static function get_read_db_config();

    static function get_table_name();

    static function get_table_pk();

    static function enable_orm_pk_cache();

    static function find();

    static function findOne();

    static function findByPk($pk);

    static function findByPks($pk);

    static function count();

    static function sum($field);

    function save();

    function delete();
}