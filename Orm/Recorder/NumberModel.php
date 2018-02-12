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

    public function record() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $obj_arr = (array)$this;
        $res = self::$data_processor->replace(array_keys($obj_arr), array_values($obj_arr));
        self::clearDataProcessor();
        return $res;
    }

    private static function clearDataProcessor() {
        self::$data_processor = null;
        self::$instance = null;
    }
}