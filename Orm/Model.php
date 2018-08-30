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
        $res = false;
        try {
            $res = self::$data_processor->find();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }

        return $res;
    }

    public static function findOne() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->find_row();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public static function findByPk($pk) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->find_by_pk($pk);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public static function findByPks($pks) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->find_by_pks($pks);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public static function count() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->find_count();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public static function sum($field) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->find_sum($field);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public function save() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->save($this);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public function saveWithLock($locks) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->updateWithLock($this, $locks);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public function update($data, $locks=[]) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->update($this, $data, $locks);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    public function delete() {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        $res = false;
        try {
            $res = self::$data_processor->delete($this);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            self::clearDataProcessor();
        }
        return $res;
    }

    /**
     * @param array ...$fields
     * @return static
     */
    public static function fields(...$fields) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_fields($fields);
        return self::get_instance();
    }

    /**
     * @param $name
     * @param $op
     * @param $value
     * @return static
     */
    public static function where($name, $op, $value) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_filters($name, $op, $value);
        return self::get_instance();
    }

    /**
     * @param $whereRaw
     * @param $values
     * @return static
     */
    public static function whereRaw($whereRaw, $values) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_filtersRaw($whereRaw, $values);
        return self::get_instance();
    }

    /**
     * @param $field
     * @param $type
     * @return static
     */
    public static function order($field, $type) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->add_orders($field, $type);
        return self::get_instance();
    }

    /**
     * @param $offset
     * @return static
     */
    public static function offset($offset) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_offset($offset);
        return self::get_instance();
    }

    /**
     * @param $limit
     * @return static
     */
    public static function limit($limit) {
        if (is_null(self::$data_processor)) {
            self::$data_processor = new DataProcessor(get_called_class());
        }
        self::$data_processor->set_limit($limit);
        return self::get_instance();
    }

    /**
     * @return static
     */
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