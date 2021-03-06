<?php
namespace JAF\Orm\V2;

use JAF\Core\DB\Manager;
use JAF\Exception\FrameException;

class DataProcessor {
    private $model_name;
    private $table_base;
    private $table_suffix = '';
    private $table;
    private $pk_column;
    private $fields_arr = [];
    private $fields = '*';
    private $filters;
    private $orders;
    private $offset = 0;
    private $limit = self::LIMIT_MAX;

    private $sql;
    private $sql_where;
    private $params = [];
    private $sql_order;
    private $sql_limit;

    private $force_master = false;

    private $enable_orm_pk_cache;

    const FILTER_OP_RAW = 'RAW';
    const FILTER_OP_IN = 'IN';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    const LIMIT_MAX = 500;

    const INSERT_FAIL_RESULT = 'fail';

    public function __construct($model_name) {
        $this->model_name = $model_name;
        $this->table_base = $model_name::get_table_name();
        $this->table = $this->table_base . $this->table_suffix;
        $this->pk_column = $model_name::get_table_pk();
        $this->set_enable_orm_pk_cache();
    }

    private function set_enable_orm_pk_cache() {
        try {
            $enable_global_orm_pk_cache = jconfig('enable_global_orm_pk_cache', 'orm');
        } catch (\Exception $e) {
            $enable_global_orm_pk_cache = false;
        }
        $model = $this->model_name;
        $enable_model_orm_pk_cache = $model::enable_orm_pk_cache();
        $this->enable_orm_pk_cache = $enable_global_orm_pk_cache && $enable_model_orm_pk_cache;
    }

    public function set_table_suffix($table_suffix) {
        if ($this->table_suffix === '') {
            $this->table_suffix = $table_suffix;
            $this->table = $this->table_base . $table_suffix;
        }
    }

    public function find() {
        $write_or_not = $this->force_master ? true : false;
        $fields = $this->get_fields();
        $this->sql = "SELECT {$fields} FROM `{$this->table}` ";
        $this->append_where();
        $this->append_order();
        $this->append_limit();
        $this->sql .= $this->sql_where . $this->sql_order . $this->sql_limit;
        return $this->res_to_model_objs($this->execute_sql($this->sql, $this->params, $write_or_not));
    }

    public function find_row() {
        $write_or_not = $this->force_master ? true : false;
        $fields = $this->get_fields();
        $this->limit = 1;
        $this->sql = "SELECT {$fields} FROM `{$this->table}` ";
        $this->append_where();
        $this->append_order();
        $this->append_limit();
        $this->sql .= $this->sql_where . $this->sql_order . $this->sql_limit;
        $res = $this->res_to_model_objs($this->execute_sql($this->sql, $this->params, $write_or_not));
        return array_pop($res);
    }

    public function find_by_pk($pk) {
        $write_or_not = $this->force_master ? true : false;
        //todo cache
        $this->sql = "SELECT * FROM `{$this->table}` WHERE `{$this->pk_column}`=?";
        $res = $this->res_to_model_objs($this->execute_sql($this->sql, [$pk], $write_or_not));
        return array_pop($res);
    }

    public function find_by_pks($pks) {
        $write_or_not = $this->force_master ? true : false;
        //todo cache
        $this->sql = "SELECT * FROM `{$this->table}` WHERE `{$this->pk_column}` IN (".str_repeat('?,', count($pks) - 1)."?)";
        return $this->res_to_model_objs($this->execute_sql($this->sql, $pks, $write_or_not));
    }

    public function find_count() {
        $write_or_not = $this->force_master ? true : false;
        $this->sql = "SELECT COUNT(1) AS `cnt` FROM `{$this->table}` ";
        $this->append_where();
        $this->sql .= $this->sql_where;
        $res = $this->execute_sql($this->sql, $this->params, $write_or_not);
        $res = array_pop($res);
        return intval($res['cnt']);
    }

    public function find_sum($field) {
        $write_or_not = $this->force_master ? true : false;
        $this->sql = "SELECT sum(`{$field}`) AS `sum` FROM `{$this->table}` ";
        $this->append_where();
        $this->sql .= $this->sql_where;
        $res = $this->execute_sql($this->sql, $this->params, $write_or_not);
        $res = array_pop($res);
        return $res['sum'];
    }


    public function insert(&$obj) {
        $pk_column = $this->pk_column;
        $obj_arr = (array)$obj;
        $lastInsertId = $this->insert_internal(array_keys($obj_arr), array_values($obj_arr));
        if ($lastInsertId != self::INSERT_FAIL_RESULT) {
            if (!isset($obj->$pk_column)) {
                $obj->$pk_column = $lastInsertId;
            }
            $obj->tableSuffix = $this->table_suffix;
        }
        return $obj->$pk_column;
    }

    public function update(&$obj, $data, $locks) {
        $pk_column = $this->pk_column;
        if (is_null($obj->$pk_column)) {
            throw new FrameException(FrameException::ENUM_DB_UPDATE_PK_ERR);
        }
        if (isset($obj->tableSuffix)) {
            $this->table_suffix = $obj->tableSuffix;
            $this->table = $this->table_base . $obj->tableSuffix;
        }
        $res = $this->update_by_pk(array_keys($data), array_values($data), $obj->$pk_column, $locks);
        if ($res > 0) {
            foreach ($data as $k => $v) {
                $obj->$k = $v;
            }
        }
        return $res;
    }

    public function delete(&$obj) {
        $pk_column = $this->pk_column;
        if (is_null($obj->$pk_column)) {
            throw new FrameException(FrameException::ENUM_DB_DELETE_PK_ERR);
        }
        if (isset($obj->tableSuffix)) {
            $this->table_suffix = $obj->tableSuffix;
            $this->table = $this->table_base . $obj->tableSuffix;
        }
        $write_or_not = true;
        $this->sql = "DELETE FROM `{$this->table}` WHERE `{$pk_column}`=?";
        $this->params = [$obj->$pk_column];
        return $this->execute_sql($this->sql, $this->params, $write_or_not);
    }

    private function insert_internal($fields, $values) {
        $write_or_not = true;
        $fields_str = "`".implode("`,`", $fields)."`";
        $values_str = str_repeat('?,', count($values) - 1).'?';
        $this->params = $values;
        $this->sql = "INSERT INTO `{$this->table}` ({$fields_str}) VALUES ({$values_str})";
        return $this->execute_sql($this->sql, $this->params, $write_or_not);
    }

    private function update_by_pk($fields, $values, $pk_id, $locks=[]) {
        $write_or_not = true;
        $update_str = '`'.implode('`=?,`', $fields).'`=?';
        $this->sql = "UPDATE `{$this->table}` SET {$update_str} WHERE {$this->pk_column}=?";
        $lock_values = [];
        if (!empty($locks)) {
            $lock_fields_sql = '';
            foreach ($locks as $lock_field => $lock_value) {
                $lock_fields_sql .= " AND {$lock_field}=?";
                $lock_values[] = $lock_value;
            }
            $this->sql .= $lock_fields_sql;
        }
        $this->params = array_merge($values,[$pk_id], $lock_values);
        return $this->execute_sql($this->sql, $this->params, $write_or_not);
    }

    public function set_fields($fields) {
        if (is_array($fields) && !empty($fields)) {
            array_unshift($fields, $this->pk_column);
            $fields = array_unique($fields);
            $fields_str = '';
            foreach ($fields as $field) {
                $fields_str .= '`'.$field.'`,';
            }
            $fields_str = rtrim($fields_str, ',');
            $this->fields_arr = $fields;
        } else {
            $fields_str = '*';
        }
        $this->fields = $fields_str;
    }

    private function get_fields() {
        return $this->fields;
    }

    public function add_filters($name, $op, $value) {
        $this->filters[] = [$name, $op, $value];
    }

    public function add_filtersRaw($whereRaw, $values) {
        $this->filters[] = [$whereRaw, self::FILTER_OP_RAW, $values];
    }

    private function append_where() {
        if (empty($this->filters)) return false;
        $this->sql_where = ' WHERE ';
        foreach ($this->filters as list($filter_name, $filter_op, $filter_value)) {
            $filter_op = strtoupper($filter_op);
            if ($filter_op == self::FILTER_OP_RAW) {
                $this->sql_where .= "({$filter_name}) AND ";
                $value = is_array($filter_value) ? $filter_value : [$filter_value];
            } else {
                if ($filter_op == self::FILTER_OP_IN && is_array($filter_value)) {
                    if (is_array($filter_value) && !empty($filter_value)) {
                        $this->sql_where .= "`{$filter_name}` {$filter_op} (". str_repeat('?,', count($filter_value) - 1). "?) AND ";
                        $value = $filter_value;
                    } else {
                        $this->sql_where .= "1=0 AND ";
                        $value = [];
                    }
                } else {
                    $this->sql_where .= "`{$filter_name}` {$filter_op} ? AND ";
                    $value = is_array($filter_value) ? $filter_value : [$filter_value];
                }
            }
            $this->params = array_merge($this->params, $value);
        }
        $this->sql_where = rtrim($this->sql_where, ' AND ');
        return true;
    }

    public function add_orders($field, $type) {
        $this->orders[$field] = $type;
    }

    private function append_order() {
        if (empty($this->orders)) return false;
        $this->sql_order = ' ORDER BY ';
        foreach ($this->orders as $field => $type) {
            $type = strtoupper($type);
            if ($type == self::ORDER_ASC || $type == self::ORDER_DESC) {
                $this->sql_order .= "`{$field}` {$type},";
            }
        }
        $this->sql_order = rtrim($this->sql_order, ',');
        return true;
    }

    public function set_offset($offset) {
        $offset = intval($offset);
        $offset = ($offset<=0) ? 0 : $offset;
        $this->offset = $offset;
    }

    public function set_limit($limit) {
        $limit = intval($limit);
        $limit = ($limit<=0) ? self::LIMIT_MAX : $limit;
        $this->limit = ($limit>self::LIMIT_MAX) ? self::LIMIT_MAX : $limit;
    }

    private function append_limit() {
        $this->sql_limit = " LIMIT {$this->limit} OFFSET {$this->offset}";
    }

    public function force_master() {
        $this->force_master = true;
    }

    public function execute_sql($sql, $params=null, $writable=false) {
        $type = $this->get_type($sql);

        if (!in_array($type, array('SELECT', 'DESC'))) $writable = true;

        $tryCount = 0;
        executeStart:

        $pdo_manager = Manager::get_instance();
        $pdo_name = $this->get_pdo_name($writable);
        $pdo = $pdo_manager->get_pdo($pdo_name);
        $stmt = $pdo->prepare($sql);

        try {
            $result = $stmt->execute((array)$params);
        } catch (\PDOException $e) {
            //处理在job中长时间断开的问题，并且只重新连接一次
            if ($tryCount===0 && (preg_match("#".'MySQL server has gone away'."#", $e->getMessage()) || $e->getMessage()=='MySQL server has gone away')) {
                $tryCount++;
                $pdo_manager->close_pdo($pdo_name);
                goto executeStart;
            } else {
                throw $e;
            }
        }

        switch($type) {
            case 'INSERT':
                $result = $pdo->lastInsertId();
                if (!$result && $stmt->rowCount() <= 0) {
                    $result = self::INSERT_FAIL_RESULT;
                }
                break;
            case 'REPLACE':
                $result = $pdo->lastInsertId();
                if (!$result) {
                    $result = $stmt->rowCount();
                }
                break;

            case 'UPDATE':
            case 'DELETE':
                $result = $stmt->rowCount();
                break;

            case 'SELECT':
            case 'DESC':
                $result = $stmt->fetchAll();
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    private function get_type($sql) {
        $type = substr($sql, 0, strpos($sql, " "));
        return trim(strtoupper($type));
    }

    private function get_pdo_name($writable) {
        $model_name = $this->model_name;
        if ($writable) {
            $pdo_name = $model_name::get_write_db_config();
        } else {
            $pdo_name = $model_name::get_read_db_config();
        }
        return $pdo_name;
    }

    private function res_to_model_objs($res) {
        $objs = [];
        foreach ($res as $r) {
            $obj = new $this->model_name();
            foreach ($r as $col => $val) {
                if (!empty($this->fields_arr) && !in_array($col, $this->fields_arr)) {
                    continue;
                }
                $obj->$col = $val;
            }
            $obj->tableSuffix = $this->table_suffix;
            $objs[] = $obj;
        }
        return $objs;
    }
}