<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

class RequestContextMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // 乱数 8byte 生成 (PHP標準 random_bytes 優先 / fallback openssl)
        if (\function_exists('random_bytes')) {
            $bytes = \random_bytes(8);
        } else {
            $bytes = (string) \openssl_random_pseudo_bytes(8);
        }
        $requestId = \bin2hex($bytes);
        $request = $request->withAttribute('request_id', $requestId);
        return $handler->handle($request);
    }
}
