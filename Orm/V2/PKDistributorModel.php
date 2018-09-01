<?php
namespace JAF\Orm\V2;

abstract class PKDistributorModel implements PKDistributorInterface {
    protected static $data_processor;

    public static function distribute() {
        $model_class = get_called_class();
        $stub = $model_class::stub();
        $stub_field = array_pop(array_keys($stub));
        $stub_value = array_pop(array_values($stub));
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor($model_class);
        }
        $res = false;
        try {
            $sql = "REPLACE INTO `{$model_class::get_table_name()}` (`{$stub_field}`) values ('{$stub_value}');";
            $res = self::$data_processor->execute_sql($sql, $params=[], $writable=true);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }

        return $res;
    }

    final static function enable_orm_pk_cache() {
        return false;
    }

    protected static function clearDataProcessor() {
        self::$data_processor = null;
    }
}