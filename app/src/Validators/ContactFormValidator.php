<?php

declare(strict_types=1);

namespace App\Validators;

class ContactFormValidator
{
    /**
     * お問い合わせフォームのバリデーション.
     *
     * @param array<string, mixed> $data フォームデータ
     * @return array<string, string> エラーメッセージの配列
     */
    public function validate(array $data): array
    {
        // データのトリム（前後の空白を除去）
        $data = $this->trimData($data);

        $errors = [];

        // 名前のバリデーション
        $errors = array_merge($errors, $this->validateName($data['name'] ?? ''));

        // メールアドレスのバリデーション
        $errors = array_merge($errors, $this->validateEmail($data['email'] ?? ''));

        // メッセージのバリデーション
        $errors = array_merge($errors, $this->validateMessage($data['message'] ?? ''));

        return $errors;
    }

    /**
     * データの前後の空白を除去.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function trimData(array $data): array
    {
        return array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);
    }

    /**
     * 名前のバリデーション.
     *
     * @param string $name
     * @return array<string>
     */
    private function validateName(string $name): array
    {
        $errors = [];

        if (empty($name)) {
            $errors[] = 'お名前を入力してください。';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = 'お名前は50文字以内で入力してください。';
        }

        return $errors;
    }

    /**
     * メールアドレスのバリデーション.
     *
     * @param string $email
     * @return array<string>
     */
    private function validateEmail(string $email): array
    {
        $errors = [];

        if (empty($email)) {
            $errors[] = 'メールアドレスを入力してください。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '正しいメールアドレスを入力してください。';
        } elseif (mb_strlen($email) > 254) {
            $errors[] = 'メールアドレスは254文字以内で入力してください。';
        }

        return $errors;
    }

    /**
     * メッセージのバリデーション.
     *
     * @param string $message
     * @return array<string>
     */
    private function validateMessage(string $message): array
    {
        $errors = [];

        if (empty($message)) {
            $errors[] = 'お問い合わせ内容を入力してください。';
        } elseif (mb_strlen($message) < 10) {
            $errors[] = 'お問い合わせ内容は10文字以上で入力してください。';
        } elseif (mb_strlen($message) > 1000) {
            $errors[] = 'お問い合わせ内容は1000文字以内で入力してください。';
        }

        return $errors;
    }

    /**
     * トリム済みデータを取得.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function getTrimmedData(array $data): array
    {
        return $this->trimData($data);
    }
}
