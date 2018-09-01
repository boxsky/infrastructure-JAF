<?php
namespace JAF\Orm\V2;

abstract class ShardingModel extends Model {
    public function insert() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $this->cal_table_suffix((array)$this);
            $res = self::$data_processor->insert($this);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    /**
     * @param $table_suffix
     * @return static
     */
    public static function set_table_suffix($table_suffix) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_table_suffix($table_suffix);
        return self::get_instance();
    }

    /**
     * @param array ...$fields
     * @return static
     */
    public static function cal_table_suffix($params) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $suffix = self::get_instance()->table_suffix_route($params);
        self::$data_processor->set_table_suffix($suffix);
        return self::get_instance();
    }

    abstract function table_suffix_route($params);
}