<?php

declare(strict_types=1);

namespace App\Logging;

use DI\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LoggerProvider
{
    public static function register(Container $container, string $logDir): void
    {
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $container->set('logger.app', function () use ($logDir): LoggerInterface {
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler($logDir . '/app.log', Logger::DEBUG));

            return $logger;
        });
        $container->set('logger.security', function () use ($logDir): LoggerInterface {
            $logger = new Logger('security');
            $logger->pushHandler(new StreamHandler($logDir . '/security.log', Logger::INFO));

            return $logger;
        });
        $container->set('logger.mail', function () use ($logDir): LoggerInterface {
            $logger = new Logger('mail');
            $logger->pushHandler(new StreamHandler($logDir . '/mail.log', Logger::INFO));

            return $logger;
        });
    }
}
