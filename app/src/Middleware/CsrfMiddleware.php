<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Response;

/**
 * CSRFミドルウェア
 * フォーム送信に対するCSRF攻撃を防止します
 */
class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Guard $csrfGuard
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->csrfGuard->process($request, $handler);
        } catch (\RuntimeException $e) {
            // CSRF検証エラーの場合、403 Forbiddenを返す
            $response = new Response();
            $response->getBody()->write(
                json_encode([
                    'error' => 'CSRF token validation failed',
                    'message' => 'セキュリティトークンが無効です。ページを再読み込みしてから再試行してください。'
                ])
            );

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(403);
        }
    }
}
