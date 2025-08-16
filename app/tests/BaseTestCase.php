<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

abstract class BaseTestCase extends TestCase
{
    /** @var App<ContainerInterface|null> */
    protected App $app;

    /** @var Guard */
    protected Guard $csrfGuard;

    protected function setUp(): void
    {
        parent::setUp();

        // セッション設定（テスト用）
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Slimアプリケーションのセットアップ
        $this->app = AppFactory::create();

        // CSRF保護の設定（テスト用にarray storageを使用）
        // 失敗時のレスポンスファクトリーを設定
        $storage = [];
        $this->csrfGuard = new Guard(
            $this->app->getResponseFactory(),
            'csrf',
            $storage,
            null,
            200,
            16,
            true
        );

        // CSRF失敗時のコールバック設定
        $this->csrfGuard->setFailureHandler(function ($request, $handler) {
            $response = $this->app->getResponseFactory()->createResponse(400);
            $response->getBody()->write('CSRF validation failed');

            return $response;
        });

        // ルートの読み込み
        $routes = require __DIR__ . '/../src/routes.php';
        $routes($this->app);

        // Twigビューの設定
        $twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);

        // Twig側ではGuardインスタンスではなく、リクエスト属性(csrf_name, csrf_value)を利用する実装に変更したためGlobal追加は不要

        $this->app->add(TwigMiddleware::create($this->app, $twig));

        // CSRFミドルウェアの追加
        $this->app->add($this->csrfGuard);
    }

    /**
     * リクエストを作成するヘルパーメソッド.
     *
     * @param array<string, mixed> $data
     */
    protected function createRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri);

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            // 送信データがあり、CSRFトークンが含まれていなければ自動付与
            if (!empty($data)) {
                $nameKey = $this->csrfGuard->getTokenNameKey();
                $valueKey = $this->csrfGuard->getTokenValueKey();
                if (!array_key_exists($nameKey, $data) || !array_key_exists($valueKey, $data)) {
                    $pair = $this->csrfGuard->generateToken();
                    $data[$nameKey] = $pair[$nameKey];
                    $data[$valueKey] = $pair[$valueKey];
                }
            }
            if (!empty($data)) {
                $request = $request->withParsedBody($data);
            }
        }

        return $request;
    }

    /**
     * アプリケーションにリクエストを送信するヘルパーメソッド.
     */
    protected function runApp(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
}
