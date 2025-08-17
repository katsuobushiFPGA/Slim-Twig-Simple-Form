<?php

declare(strict_types=1);

// アプリケーション設定を読み込み
$appConfig = require __DIR__ . '/config/config.php';

// Phinx 設定配列を返す（Config オブジェクトではなく配列）
return [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => $appConfig['env'],
        'development' => [
            'adapter' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql',
            'name' => getenv('MYSQL_DATABASE') ?: 'slim_app',
            'user' => getenv('MYSQL_USER') ?: 'slim_user',
            'pass' => getenv('MYSQL_PASSWORD') ?: 'slim_password',
            'port' => (int) (getenv('MYSQL_PORT') ?: '3306'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'production' => [
            'adapter' => 'mysql',
            'host' => getenv('MYSQL_HOST'),
            'name' => getenv('MYSQL_DATABASE'),
            'user' => getenv('MYSQL_USER'),
            'pass' => getenv('MYSQL_PASSWORD'),
            'port' => (int) (getenv('MYSQL_PORT') ?: '3306'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: 'mysql',
            'name' => getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ? getenv('MYSQL_DATABASE') . '_test' : 'slim_app_test'),
            'user' => getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'slim_user',
            'pass' => getenv('DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: 'slim_password',
            'port' => (int) (getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: '3306'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];
