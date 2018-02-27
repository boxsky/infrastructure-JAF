<?php
namespace JAF\Orm\Recorder;

use JAF\Orm\DataProcessor;
abstract class NumberModel implements NumberModelInterface {
    private static $data_processor;

    protected static $instance;
    public static function get_instance() {
        if (!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return static::$instance;
    }

    public function replace() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $obj_arr = (array)$this;

        $write_or_not = true;
        $fields_str = "`".implode("`,`", array_keys($obj_arr))."`";
        $values_str = str_repeat('?,', count( array_values($obj_arr)) - 1).'?';
        $params = array_values($obj_arr);

        $sql = "REPLACE INTO `{$this::get_table_name()}` ({$fields_str}) VALUES ({$values_str})";
        $res = self::$data_processor->execute_sql($sql, $params, $write_or_not);
        self::clearDataProcessor();
        return $res;
    }

    private static function clearDataProcessor() {
        self::$data_processor = null;
        self::$instance = null;
    }
}