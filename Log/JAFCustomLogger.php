<?php
namespace JAF\Log;

class JAFCustomLogger {
    private static $instance;
    private $loggers;

    public static function &get_instance() {
        if (!self::$instance) {
            self::$instance = new JAFCustomLogger();
        }
        return self::$instance;
    }

    public function __construct() {
        $config = jconfig(null, 'log4php');
        require_once FRAME_VENDER_PATH.'apache/log4php/src/main/php/Logger.php';
        \Logger::configure($config);
    }

    public function error($msg) {
        if (!isset($this->loggers['errorLogger'])) {
            $this->loggers['errorLogger'] = \Logger::getLogger('errorLogger');
        }
        $log = $this->loggers['errorLogger'];
        $log->error($msg);
    }

    public function warn($msg) {
        if (!isset($this->loggers['warningLogger'])) {
            $this->loggers['warningLogger'] = \Logger::getLogger('warningLogger');
        }
        $log = $this->loggers['warningLogger'];
        $log->warn($msg);
    }
}