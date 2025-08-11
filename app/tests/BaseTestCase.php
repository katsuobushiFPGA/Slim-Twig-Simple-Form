<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

abstract class BaseTestCase extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Slimアプリケーションのセットアップ
        $this->app = AppFactory::create();
        
        // Twigビューの設定
        $twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
        $this->app->add(TwigMiddleware::create($this->app, $twig));
        
        // ルートの読み込み
        $routes = require __DIR__ . '/../src/routes.php';
        $routes($this->app);
    }

    /**
     * リクエストを作成するヘルパーメソッド
     */
    protected function createRequest(string $method, string $uri, array $data = []): ServerRequestInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest($method, $uri);
        
        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $request = $request->withParsedBody($data);
        }
        
        return $request;
    }

    /**
     * アプリケーションにリクエストを送信するヘルパーメソッド
     */
    protected function runApp(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }
}
