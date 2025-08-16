<?php

declare(strict_types=1);

namespace App\Repository;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use RuntimeException;

/**
 * お問い合わせテーブルのリポジトリクラス（Laravel Query Builder版）.
 */
class ContactRepository
{
    public function __construct(
        private Capsule $capsule
    ) {}

    /**
     * お問い合わせを保存.
     *
     * @param array{name: string, email: string, message: string, request_id?: string|null} $contactData
     * @return int 挿入されたレコードのID
     * @throws RuntimeException データベースエラー時
     */
    public function save(array $contactData): int
    {
        $data = [
            'name' => $contactData['name'],
            'email' => $contactData['email'],
            'message' => $contactData['message'],
            'status' => 'pending', // デフォルト状態
            'request_id' => $contactData['request_id'] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        return $this->capsule->table('contacts')->insertGetId($data);
    }

    /**
     * IDでお問い合わせを取得.
     *
     * @param int $id
     * @return array<string, mixed>|null 見つからない場合はnull
     */
    public function findById(int $id): ?array
    {
        $result = $this->capsule->table('contacts')
            ->where('id', $id)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * request_idでお問い合わせを取得.
     *
     * @param string $requestId
     * @return array<string, mixed>|null 見つからない場合はnull
     */
    public function findByRequestId(string $requestId): ?array
    {
        $result = $this->capsule->table('contacts')
            ->where('request_id', $requestId)
            ->first();

        return $result ? (array) $result : null;
    }

    /**
     * お問い合わせ一覧を取得（ページネーション対応）.
     *
     * @param int $limit 取得件数上限
     * @param int $offset 取得開始位置
     * @param string|null $status 状態フィルター（optional）
     * @return array<int, array<string, mixed>>
     */
    public function list(int $limit = 20, int $offset = 0, ?string $status = null): array
    {
        $query = $this->capsule->table('contacts')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return array_map(function ($item) {
            return (array) $item;
        }, $query->get()->toArray());
    }

    /**
     * お問い合わせの総件数を取得.
     *
     * @param string|null $status 状態フィルター（optional）
     * @return int
     */
    public function count(?string $status = null): int
    {
        $query = $this->capsule->table('contacts');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    /**
     * お問い合わせのステータスを更新.
     *
     * @param int $id
     * @param string $status pending, processed, completed のいずれか
     * @return bool 更新成功時true
     * @throws RuntimeException 不正なステータス時
     */
    public function updateStatus(int $id, string $status): bool
    {
        // statusの値を検証
        $allowedStatuses = ['pending', 'processed', 'completed'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new RuntimeException(
                sprintf('Invalid status "%s". Allowed: %s', $status, implode(', ', $allowedStatuses))
            );
        }

        $affected = $this->capsule->table('contacts')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => Carbon::now(),
            ]);

        return $affected > 0;
    }
}
