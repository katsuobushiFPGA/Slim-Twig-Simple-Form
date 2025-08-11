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
];
