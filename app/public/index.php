<?php
// セッション開始（CSRF保護に必要）
session_start();

// autoload.phpが存在しない場合のエラーハンドリング
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
require $autoloadPath;

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Csrf\Guard;
use App\Middleware\CsrfMiddleware;

// Slimアプリケーションの作成
$app = AppFactory::create();

// CSRF保護の設定（セッション使用）
$csrfGuard = new Guard($app->getResponseFactory());

// ルート定義を読み込む
(require __DIR__ . '/../src/routes.php')($app);

// Twigビューの設定
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

// CSRFトークンをTwigで利用可能にするミドルウェア
$twig->getEnvironment()->addGlobal('csrf', $csrfGuard);

$app->add(TwigMiddleware::create($app, $twig));

// CSRFミドルウェアの追加（フォーム送信ルートに適用）
$app->add($csrfGuard);

// ミドルウェアの追加
$app->addErrorMiddleware(true, true, true);

$app->run();
