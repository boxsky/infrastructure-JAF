<?php
$config['xhj_dev'] = array (
    'dsn'=>'mysql:host=192.168.10.119;dbname=airent_new_2017',
    'username' => 'root',
    'password' => 'root',
    'init_statements' => ['SET CHARACTER SET utf8', 'SET NAMES utf8'],
    'driver_options' => [PDO::ATTR_TIMEOUT=>2]
);
