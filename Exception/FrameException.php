<?php
namespace JAF\Exception;

class FrameException extends \Exception {
    const DEFAULT_CODE_MSG = 'Frame Exception!';
    /**
     * 配置异常
     */
    const ENUM_JCONFIG_PARAM_FILE_NULL = 100001;
    const ENUM_JCONFIG_FILE_NOT_EXIST  = 100002;
    const ENUM_JCONFIG_NAME_NOT_EXIST  = 100003;

    /**
     * YAC异常
     */
    const ENUM_USE_YAC_BUT_YAC_DISABLE = 100101;

    /**
     * DB异常
     */
    const ENUM_DB_CONFIG_ERR           = 100201;
    const ENUM_DB_EXEC_ERR             = 100202;

    /**
     * redis异常
     */
    const ENUM_REDIS_CONNECT_ERR       = 100301;
    const ENUM_REDIS_CONFIG_ERR        = 100302;
    const ENUM_REDIS_AUTH_FAIL         = 100303;

    public function __construct($code, $detail_info='') {
        $enum_code_msg_mappings = self::get_enum_code_msg_mappings();
        $msg = isset($enum_code_msg_mappings[$code]) ? $enum_code_msg_mappings[$code] : self::DEFAULT_CODE_MSG;
        if ($detail_info) $msg .= '{{detail_info:'.$detail_info.'}}';
        parent::__construct($msg, $code);
    }

    private static function get_enum_code_msg_mappings() {
        return array(
            self::ENUM_JCONFIG_PARAM_FILE_NULL => 'jconfig lack of param: file!',
            self::ENUM_JCONFIG_FILE_NOT_EXIST  => 'jconfig file not exist!',
            self::ENUM_JCONFIG_NAME_NOT_EXIST  => 'jconfig name not exist!',
            self::ENUM_USE_YAC_BUT_YAC_DISABLE => 'use yac but yac disable!',
            self::ENUM_DB_CONFIG_ERR           => 'db config error!',
            self::ENUM_DB_EXEC_ERR             => 'db exec error!'
        );
    }
}