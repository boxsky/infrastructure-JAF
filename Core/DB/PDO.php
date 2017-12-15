<?php
namespace JAF\Core\DB;

class PDO extends \PDO {
    private $name;
    private $default_fetch_mode=PDO::FETCH_ASSOC;

    public function __construct($dsn, $username, $password, $options) {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function set_pdo_name($name) {
        $this->name = $name;
    }

    public function prepare($statement, $driver_options=[]) {
        $stmt = parent::prepare($statement, $driver_options);
        $stmt->setFetchMode($this->default_fetch_mode);
        return $stmt;
    }
}