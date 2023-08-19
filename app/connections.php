<?php

return [
    'db' => [
        'driver' => 'pdo_mysql',
        'host' => env('DB_HOST'),
        'port' => 3306,
        'dbname' => env('DB_NAME'),
        'user' => 'root',
        'password' => env('DB_ROOT_PASSWORD'),
        'charset' => 'utf8mb4',
        'driverOptions' => [
            1002 => 'SET NAMES utf8mb4'
        ]
    ]
];
