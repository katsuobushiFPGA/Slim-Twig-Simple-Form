<?php

declare(strict_types=1);

namespace Tests;

class IntegrationTest extends BaseTestCase
{
    public function testCompleteFormFlow(): void
    {
        // 1. ホームページにアクセス
        $request = $this->createRequest('GET', '/');
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        // 2. 入力画面にアクセス
        $request = $this->createRequest('GET', '/form/input');
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('ステップ 1/3', (string) $response->getBody());

        // 3. フォームデータを送信して確認画面へ
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これは統合テストのメッセージです。',
        ];

        $request = $this->createRequest('POST', '/form/confirm', $formData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $confirmBody = (string) $response->getBody();
        $this->assertStringContainsString('ステップ 2/3', $confirmBody);
        $this->assertStringContainsString('テスト太郎', $confirmBody);
        $this->assertStringContainsString('test@example.com', $confirmBody);
        $this->assertStringContainsString('これは統合テストのメッセージです。', $confirmBody);

        // 4. 送信を実行して完了画面へ
        $request = $this->createRequest('POST', '/form/complete', $formData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $completeBody = (string) $response->getBody();
        $this->assertStringContainsString('ステップ 3/3', $completeBody);
        $this->assertStringContainsString('お問い合わせありがとうございます！', $completeBody);
        $this->assertStringContainsString('テスト太郎', $completeBody);
        $this->assertStringContainsString('test@example.com', $completeBody);
        $this->assertStringContainsString('これは統合テストのメッセージです。', $completeBody);
    }

    public function testFormFlowWithValidationError(): void
    {
        // 1. 入力画面にアクセス
        $request = $this->createRequest('GET', '/form/input');
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        // 2. 不正なデータを送信
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'message' => '',
        ];

        $request = $this->createRequest('POST', '/form/confirm', $invalidData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $errorBody = (string) $response->getBody();
        // バリデーションエラーで入力画面に戻る
        $this->assertStringContainsString('ステップ 1/3', $errorBody);
        $this->assertStringContainsString('入力エラー', $errorBody);

        // 3. 正しいデータで再送信
        $validData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => '修正後のメッセージです。',
        ];

        $request = $this->createRequest('POST', '/form/confirm', $validData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $confirmBody = (string) $response->getBody();
        $this->assertStringContainsString('ステップ 2/3', $confirmBody);
        $this->assertStringContainsString('テスト太郎', $confirmBody);
    }

    public function testFormModificationFlow(): void
    {
        // 1. 確認画面まで進む
        $formData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これは最初のテストメッセージです。',
        ];

        $request = $this->createRequest('POST', '/form/confirm', $formData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('ステップ 2/3', (string) $response->getBody());

        // 2. 修正ボタンで入力画面に戻る（POST /form/input）
        $request = $this->createRequest('POST', '/form/input', $formData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $inputBody = (string) $response->getBody();
        $this->assertStringContainsString('ステップ 1/3', $inputBody);
        // データが保持されている
        $this->assertStringContainsString('value="テスト太郎"', $inputBody);
        $this->assertStringContainsString('これは最初のテストメッセージです。', $inputBody);

        // 3. 修正後のデータで再度確認画面へ
        $modifiedData = [
            'name' => 'テスト次郎',
            'email' => 'test2@example.com',
            'message' => 'これは修正後のテストメッセージです。',
        ];

        $request = $this->createRequest('POST', '/form/confirm', $modifiedData);
        $response = $this->runApp($request);
        $this->assertEquals(200, $response->getStatusCode());

        $confirmBody = (string) $response->getBody();
        $this->assertStringContainsString('テスト次郎', $confirmBody);
        $this->assertStringContainsString('test2@example.com', $confirmBody);
        $this->assertStringContainsString('これは修正後のテストメッセージです。', $confirmBody);
    }

    public function testDirectAccessToComplete(): void
    {
        // 完了画面に直接GETアクセスした場合のリダイレクト
        $request = $this->createRequest('GET', '/form/complete');
        $response = $this->runApp($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/form/input', $response->getHeaderLine('Location'));
    }
}
