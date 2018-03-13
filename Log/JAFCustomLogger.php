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

    public function error($msg, $logger='errorLogger') {
        if (!isset($this->loggers[$logger])) {
            $this->loggers[$logger] = \Logger::getLogger($logger);
        }
        $this->loggers[$logger]->error($msg);
    }

    public function warn($msg, $logger='warningLogger') {
        if (!isset($this->loggers[$logger])) {
            $this->loggers[$logger] = \Logger::getLogger($logger);
        }
        $this->loggers[$logger]->warn($msg);
    }
}