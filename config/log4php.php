<?php
// default log file path
$config['php_log_file'] = '/var/log/'.APP_NAME.'/php.log';
$config['error_log_file'] = '/var/log/'.APP_NAME.'/error.log';
$config['warning_log_file'] = '/var/log/'.APP_NAME.'/warning.log';

//custom log file path in app
$log4php_file_config = jconfig(null, 'log4php_file');
$config = array_merge($config, $log4php_file_config);

//log4php config
$config = [
    'rootLogger' => [
        'appenders' => ['default']
    ],
    'loggers' => [
        'errorLogger' => [
            'additivity' => 'false',
            'level' => 'error',
            'appenders' => ['errorAppender']
        ],
        'warningLogger' => [
            'additivity' => 'false',
            'level' => 'warn',
            'appenders' => ['warngingAppender']
        ]
    ],
    'appenders' => [
        'default' => [
            'class' => 'LoggerAppenderDailyFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => '%msg%n'
                ]
            ],
            'params' => [
                'datePattern' => 'Y-m-d',
                'file' => $config['php_log_file'].'-%s'
            ]
        ],
        'errorAppender' => [
            'class' => 'LoggerAppenderDailyFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => '%msg%n'
                ]
            ],
            'params' => [
                'datePattern' => 'Y-m-d',
                'file' => $config['error_log_file'].'-%s'
            ]
        ],
        'warngingAppender' => [
            'class' => 'LoggerAppenderDailyFile',
            'layout' => [
                'class' => 'LoggerLayoutPattern',
                'params' => [
                    'conversionPattern' => '%msg%n'
                ]
            ],
            'params' => [
                'datePattern' => 'Y-m-d',
                'file' => $config['warning_log_file'].'-%s'
            ]
        ]
    ]
];