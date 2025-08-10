<?php
// autoload.phpが存在しない場合のエラーハンドリング
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
require $autoloadPath;

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Slimアプリケーションの作成
$app = AppFactory::create();

// ルート定義を読み込む
(require __DIR__ . '/../src/routes.php')($app);

// Twigビューの設定
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

// ミドルウェアの追加
$app->addErrorMiddleware(true, true, true);

$app->run();
