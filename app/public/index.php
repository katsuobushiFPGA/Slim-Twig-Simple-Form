<?php

declare(strict_types=1);
// セッション開始（CSRF保護）
session_start();
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../src/bootstrap.php';
$app->run();
