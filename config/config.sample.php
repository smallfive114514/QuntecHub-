<?php
return [
    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => '',
        'user'    => '',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'      => 'QuntecHub 项目文件管理系统',
        'upload_dir'=> dirname(__DIR__).'/public/uploads',
        'max_size'  => 0,
        'allow_ext' => [],
    ],
    'installed' => false,
];
