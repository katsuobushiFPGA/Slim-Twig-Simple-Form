<?php

declare(strict_types=1);

use App\Error\GeneralErrorRenderer;
use App\Error\HttpErrorRenderer;
use App\Logging\LoggerProvider;
use App\Middleware\RequestContextMiddleware;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

/*
 * アプリケーション初期化（コンテナ / ルート / ミドルウェア / エラーハンドラ）
 * public/index.php から require され Slim\App を返す。
 */
return (function (): App {
    $container = new Container();
    AppFactory::setContainer($container);
    $app = AppFactory::create();
    // 設定読み込み
    /** @var array{env:string,debug:bool,paths:array{log:string,templates:string}} $config */
    $config = require __DIR__ . '/../config/config.php';
    $container->set('config', $config);

    // ログ設定 + リクエストID付与
    LoggerProvider::register($container, $config['paths']['log']);
    $app->add(new RequestContextMiddleware());

    // CSRF Guard 設定（失敗時セキュリティログ）
    $csrfGuard = new Guard($app->getResponseFactory());
    $securityLogger = $container->get('logger.security');
    $csrfGuard->setFailureHandler(function (Request $request, RequestHandler $handler) use ($app, $securityLogger) {
        $context = [
            'request_id' => $request->getAttribute('request_id'),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null,
            'ua' => $request->getHeaderLine('User-Agent'),
            'path' => (string) $request->getUri(),
        ];
        $securityLogger->warning('CSRF token validation failed', $context);
        $response = $app->getResponseFactory()->createResponse(400);
        $response->getBody()->write('CSRF validation failed');

        return $response->withHeader('Content-Type', 'text/plain');
    });

    // ルート定義
    (require __DIR__ . '/routes.php')($app);

    // Twig
    $twig = Twig::create($config['paths']['templates'], ['cache' => false]);
    $twig->getEnvironment()->addGlobal('csrf', $csrfGuard);
    $app->add(TwigMiddleware::create($app, $twig));

    // CSRF ミドルウェア
    $app->add($csrfGuard);

    // エラーハンドラ
    $errorMiddleware = $app->addErrorMiddleware($config['debug'], true, $config['debug']);
    $httpHandler = new HttpErrorRenderer($twig, true);
    $generalHandler = new GeneralErrorRenderer($twig, true);
    $errorMiddleware->setDefaultErrorHandler($generalHandler);
    $errorMiddleware->setErrorHandler(\Slim\Exception\HttpException::class, $httpHandler);

    return $app;
})();
