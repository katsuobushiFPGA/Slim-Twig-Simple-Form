<?php

declare(strict_types=1);

namespace App\Database;

use DI\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * データベース接続設定用のプロバイダー（Laravel Database使用）.
 */
class DatabaseProvider
{
    /**
     * データベース関連のサービスをコンテナに登録.
     *
     * @param Container $container DIコンテナ
     * @param array<string, mixed> $config アプリケーション設定
     */
    public static function register(Container $container, array $config): void
    {
        // Laravel Database Capsule のセットアップ
        $capsule = new Capsule();

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $config['database']['host'],
            'port' => $config['database']['port'],
            'database' => $config['database']['dbname'],
            'username' => $config['database']['username'],
            'password' => $config['database']['password'],
            'charset' => $config['database']['charset'],
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Eloquent ORM のブート（オプション）
        $capsule->bootEloquent();

        // グローバルでも使用可能にする
        $capsule->setAsGlobal();

        // DIコンテナに登録
        $container->set(Capsule::class, $capsule);

        // PDO インスタンスの登録（Capsuleから取得）
        $container->set('db', function (Container $c) {
            return $c->get(Capsule::class)->getConnection()->getPdo();
        });

        // PDO クラス名でも取得できるよう登録
        $container->set(\PDO::class, function (Container $c) {
            return $c->get('db');
        });

        // Query Builder のファクトリー
        $container->set('query', function (Container $c) {
            return $c->get(Capsule::class)->table(''); // テーブル名は後で指定
        });
    }
}
