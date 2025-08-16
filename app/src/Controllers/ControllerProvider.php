<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repository\ContactRepository;
use DI\Container;
use Psr\Log\LoggerInterface;

/**
 * コントローラー関連のサービスをDIコンテナに登録するプロバイダー.
 */
class ControllerProvider
{
    /**
     * コントローラークラスをコンテナに登録.
     *
     * @param Container $container DIコンテナ
     */
    public static function register(Container $container): void
    {
        // FormController の登録
        $container->set(FormController::class, function (Container $c) {
            return new FormController(
                $c->get(ContactRepository::class),
                $c->get('logger.app') // アプリケーションロガーを注入
            );
        });
    }
}
