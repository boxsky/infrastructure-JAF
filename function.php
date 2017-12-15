<?php
function is_yac_enable() {
    return (defined('YAC_ENABLE') && YAC_ENABLE);
}

function jconfig($name, $file) {
    if (is_null($file)) {
        throw new JAF\Exception\FrameException(JAF\Exception\FrameException::ENUM_JCONFIG_PARAM_FILE_NULL);
    }
    if (is_yac_enable()) {
        $localSharedMemory_client = JAF\Cache\LocalSharedMemory::get_instance();
        $config_key = 'jconfig::' . $file;
        $config = $localSharedMemory_client->get($config_key);
        if ($config === false) {
            $config = load_config($file);
            $ttl = (!defined('ENV') || ENV == 'dev') ? 5 : 86400;
            $localSharedMemory_client->set($config_key, $config, $ttl);
        }
    } else {
        $config = load_config($file);
    }
    if (is_null($config)) {
        throw new JAF\Exception\FrameException(JAF\Exception\FrameException::ENUM_JCONFIG_FILE_NOT_EXIST, 'file is '.$file);
    }
    if (!is_null($name)) {
        if (!isset($config[$name])) {
            throw new JAF\Exception\FrameException(JAF\Exception\FrameException::ENUM_JCONFIG_NAME_NOT_EXIST, 'name is '.$name);
        }
        $res = $config[$name];
    } else {
        $res = $config;
    }
    return $res;
}

$_JCONFIGURES = [];
function load_config($file) {
    $config = null;
    global $_JCONFIGURES;
    if (!isset($_JCONFIGURES[$file])) {
        $LOOKUP_CONF_PATH = [
            FRAME_PATH . 'config/',
            APP_CONFIG_PATH,
            EXT_CONFIG_PATH . 'common/',
            EXT_CONFIG_PATH . APP_NAME . '/'
        ];
        foreach ($LOOKUP_CONF_PATH as $config_path) {
            $complete_path = $config_path.$file.'.php';
            if (file_exists($complete_path)) {
                require_once $complete_path;
            }
        }
        $_JCONFIGURES[$file] = $config;
    } else {
        $config = $_JCONFIGURES[$file];
    }
    return $config;
}

function build_static_resource_url($resource) {
    $request = JAF\Core\APP::get_instance()->get_request();
    $schema = (is_callable(array($request, 'is_secure')) && $request->is_secure()) ? 'https://' : 'http://';
    $host = jconfig("cdn_host", "resource");
    $path = jconfig("cdn_path", "resource");
    return $schema.$host.$path.$resource;
}

function friendly_error_type($error_type) {
    switch ($error_type) {
        case E_ERROR: // 1
            $err_str = 'E_ERROR';
            break;
        case E_WARNING: // 2
            $err_str = 'E_WARNING';
            break;
        case E_PARSE: // 4
            $err_str = 'E_PARSE';
            break;
        case E_NOTICE: // 8
            $err_str = 'E_NOTICE';
            break;
        case E_CORE_ERROR: // 16
            $err_str = 'E_CORE_ERROR';
            break;
        case E_CORE_WARNING: // 32
            $err_str = 'E_CORE_WARNING';
            break;
        case E_COMPILE_ERROR: // 64
            $err_str = 'E_COMPILE_ERROR';
            break;
        case E_COMPILE_WARNING: // 128
            $err_str = 'E_COMPILE_WARNING';
            break;
        case E_USER_ERROR: // 256
            $err_str = 'E_USER_ERROR';
            break;
        case E_USER_WARNING: // 512
            $err_str = 'E_USER_WARNING';
            break;
        case E_USER_NOTICE: // 1024
            $err_str = 'E_USER_NOTICE';
            break;
        case E_STRICT: // 2048
            $err_str = 'E_STRICT';
            break;
        case E_RECOVERABLE_ERROR: // 4096
            $err_str = 'E_RECOVERABLE_ERROR';
            break;
        case E_DEPRECATED: // 8192
            $err_str = 'E_DEPRECATED';
            break;
        case E_USER_DEPRECATED: // 16384
            $err_str = 'E_USER_DEPRECATED';
            break;
        default:
            $err_str = 'UNDEFINED';
            break;
    }
    return $err_str;
}

function jaf_warning_handler($err_type, $err_msg, $err_file, $err_line) {
    //todo custom user triggered error by level

    $logger = \JAF\Log\JAFCustomLogger::get_instance();
    $log_msg = format_error_log(php_sapi_name(), $err_type, $err_file, $err_line, $err_msg);
    $logger->warn($log_msg);
}

function jaf_fatal_handler() {
    //todo error page

    $error_info = error_get_last();
    if (!is_null($error_info)) {
        $logger = \JAF\Log\JAFCustomLogger::get_instance();
        $log_msg = format_error_log(php_sapi_name(), $error_info['type'], $error_info['file'], $error_info['line'], $error_info['message']);
        $logger->error($log_msg);
    }
}

function format_error_log($sapi, $err_type, $err_file, $err_line, $err_msg) {
    $function = ($sapi == 'cli') ? 'format_error_log_cli' : 'format_error_log_fpm';
    return call_user_func($function, $err_type, $err_file, $err_line, $err_msg);
}

function format_error_log_cli($err_type, $err_file, $err_line, $err_msg) {
    global $argv;
    $log_msg = " app: " . APP_NAME . "\n";
    $log_msg .= "from: " . "cli\n";
    $log_msg .= "type: " . friendly_error_type($err_type) . "\n";
    $log_msg .= "file: " . $err_file . "\n";
    $log_msg .= "line: " . $err_line . "\n";
    $log_msg .= "argv: " . json_encode($argv) . "\n";
    $log_msg .= "time: " . date('Y-m-d H:i:s') . "\n";
    $log_msg .= "uniq: " . gethostname() . "-" . getmypid() . "-" . str_replace('.', '', microtime(true)) . "\n";
    $log_msg .= " msg: " . $err_msg . "\n";
    return $log_msg;
}

function format_error_log_fpm($err_type, $err_file, $err_line, $err_msg) {
    $log_msg = " app: " . APP_NAME . "\n";
    $log_msg .= "from: " . "fpm\n";
    $log_msg .= "type: " . friendly_error_type($err_type) . "\n";
    $log_msg .= "host: " . $_SERVER['SERVER_NAME'] . "\n";
    $log_msg .= " uri: " . $_SERVER['REQUEST_URI'] . "\n";
    $log_msg .= "file: " . $err_file . "\n";
    $log_msg .= "line: " . $err_line . "\n";
    $log_msg .= "time: " . date('Y-m-d H:i:s') . "\n";
    $log_msg .= "uniq: " . gethostname() . "-" . getmypid() . "-" . str_replace('.', '', microtime(true)) . "\n";
    $log_msg .= " msg: " . $err_msg . "\n";
    return $log_msg;
}