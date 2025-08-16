<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateContactsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // contactsテーブルを作成
        $table = $this->table('contacts', [
            'id' => false,  // デフォルトのIDカラムを無効にする
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => 'お問い合わせテーブル',
        ]);

        $table
            // ID - BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY（手動定義）
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed' => false,
                'null' => false,
                'comment' => 'プライマリキー',
            ])
            // お名前 - ContactFormValidatorで50文字制限あり
            ->addColumn('name', 'string', [
                'limit' => 50,
                'null' => false,
                'comment' => 'お名前',
            ])
            // メールアドレス - RFC準拠で320文字だが255で十分
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
                'comment' => 'メールアドレス',
            ])
            // お問い合わせ内容 - ContactFormValidatorで1000文字制限あり
            ->addColumn('message', 'text', [
                'null' => false,
                'comment' => 'お問い合わせ内容',
            ])
            // 処理状況 - pending(初期値), processed(処理中), completed(完了)
            ->addColumn('status', 'enum', [
                'values' => ['pending', 'processed', 'completed'],
                'default' => 'pending',
                'null' => false,
                'comment' => '処理状況',
            ])
            // リクエストID - RequestContextMiddlewareで生成される16桁のID
            ->addColumn('request_id', 'string', [
                'limit' => 16,
                'null' => true,
                'comment' => 'トレース用リクエストID',
            ])
            // タイムスタンプ
            ->addTimestamps('created_at', 'updated_at')
            // インデックス - 処理状況での検索用
            ->addIndex(['status'], ['name' => 'idx_status'])
            // インデックス - 作成日時での検索・ソート用
            ->addIndex(['created_at'], ['name' => 'idx_created_at'])
            // インデックス - リクエストIDでのトレース用
            ->addIndex(['request_id'], ['name' => 'idx_request_id'])
            ->create();
    }
}
