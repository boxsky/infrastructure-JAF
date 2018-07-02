<?php
namespace JAF\Orm;

abstract class Model implements ModelInterface {
    protected static $data_processor;

    /**
     * @return static
     */
    protected static $instance;
    public static function get_instance() {
        if (!self::$instance instanceof static) {
            self::$instance = new static();
        }
        return static::$instance;
    }

    public static function find() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find();
        self::clearDataProcessor();
        return $res;
    }

    public static function findOne() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find_row();
        self::clearDataProcessor();
        return $res;
    }

    public static function findByPk($pk) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find_by_pk($pk);
        self::clearDataProcessor();
        return $res;
    }

    public static function findByPks($pks) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find_by_pks($pks);
        self::clearDataProcessor();
        return $res;
    }

    public static function count() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find_count();
        self::clearDataProcessor();
        return $res;
    }

    public static function sum($field) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->find_sum($field);
        self::clearDataProcessor();
        return $res;
    }

    public function save() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->save($this);
        self::clearDataProcessor();
        return $res;
    }

    public function saveWithLock($locks) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->updateWithLock($this, $locks);
        self::clearDataProcessor();
        return $res;
    }

    public function update($data, $locks=[]) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->update($this, $data, $locks);
        self::clearDataProcessor();
        return $res;
    }

    public function delete() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = self::$data_processor->delete($this);
        self::clearDataProcessor();
        return $res;
    }

    public static function fields(...$fields) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_fields($fields);
        return self::get_instance();
    }

    public static function where($name, $op, $value) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_filters($name, $op, $value);
        return self::get_instance();
    }

    public static function whereRaw($whereRaw, $values) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_filtersRaw($whereRaw, $values);
        return self::get_instance();
    }

    public static function order($field, $type) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_orders($field, $type);
        return self::get_instance();
    }

    public static function offset($offset) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_offset($offset);
        return self::get_instance();
    }

    public static function limit($limit) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_limit($limit);
        return self::get_instance();
    }

    public static function forceMaster() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->force_master();
        return self::get_instance();
    }

    public static function enable_orm_pk_cache() {
        return true;
    }

    protected static function clearDataProcessor() {
        self::$data_processor = null;
        self::$instance = null;
    }
}