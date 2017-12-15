<?php
namespace JAF\Core\DB;

use JAF\Exception\FrameException;

class Manager {
    private static $instance;
    private $pdo_list;
    private static $options = [PDO::ATTR_TIMEOUT => 2];

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new Manager();
        }
        return self::$instance;
    }

    public function get_pdo($name) {
        if (!isset($this->pdo_list[$name])) {
            $this->pdo_list[$name] = $this->load_pdo($name);
        }
        return $this->pdo_list[$name];
    }

    private function load_pdo($name) {
        $db_config = jconfig($name, 'database');
        if (!isset($db_config['dsn']) || !isset($db_config['username']) || !isset($db_config['password'])) {
            throw new FrameException(FrameException::ENUM_DB_CONFIG_ERR);
        }
        $options = empty($db_config['driver_options']) ? self::$options : $db_config['driver_options'];
        $pdo = new PDO($db_config['dsn'], $db_config['username'], $db_config['password'], $options);
        $pdo->set_pdo_name($name);
        if (isset($db_config['init_statements'])) {
            foreach ($db_config['init_statements'] as $sql) {
                $pdo->exec($sql);
            }
        }
        return $pdo;
    }

    public function close_pdo($name) {
        if (isset($this->pdo_list[$name])) {
            unset($this->pdo_list[$name]);
        }
    }
}
