<?php

declare(strict_types=1);

namespace App\Repository;

use DI\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * リポジトリ関連のサービスをDIコンテナに登録するプロバイダー.
 */
class RepositoryProvider
{
    /**
     * リポジトリクラスをコンテナに登録.
     *
     * @param Container $container DIコンテナ
     */
    public static function register(Container $container): void
    {
        // ContactRepository の登録
        $container->set(ContactRepository::class, function (Container $c) {
            return new ContactRepository($c->get(Capsule::class));
        });
    }
}
