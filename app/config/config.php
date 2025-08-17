<?php

declare(strict_types=1);

// アプリ設定配列を返す。必要に応じて .env ローダーに差し替え可能。
$appEnv = getenv('APP_ENV') ?: 'development';
$debug = ($appEnv !== 'production') || getenv('APP_DEBUG') === '1';

return [
    'env' => $appEnv,
    'debug' => $debug,
    'paths' => [
        'log' => __DIR__ . '/../logs',
        'templates' => __DIR__ . '/../templates',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'mysql',
        'port' => (int) (getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306),
        'dbname' => getenv('DB_NAME') ?: getenv('DB_DATABASE') ?: getenv('MYSQL_DATABASE') ?: 'slim_app',
        'username' => getenv('DB_USER') ?: getenv('DB_USERNAME') ?: getenv('MYSQL_USER') ?: 'slim_user',
        'password' => getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: 'slim_password',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
