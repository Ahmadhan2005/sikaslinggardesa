<?php
require_once __DIR__ . '/env.php';

$config = [
    'host' => getenv('DB_HOST'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'database' => getenv('DB_NAME'),
    'charset' => 'utf8mb4',
    'port' => 3306
];

return $config;