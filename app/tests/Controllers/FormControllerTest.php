<?php
namespace Tests\Controllers;

use Tests\BaseTestCase;

class FormControllerTest extends BaseTestCase
{
    public function testIndexPage(): void
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('お問い合わせフォーム - Slim 4 + Twig', $body);
        $this->assertStringContainsString('Welcome to Slim Framework with Twig!', $body);
        $this->assertStringContainsString('お問い合わせについて', $body);
    }

    public function testInputPageGet(): void
    {
        $request = $this->createRequest('GET', '/form/input');
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('お問い合わせ入力', $body);
        $this->assertStringContainsString('ステップ 1/3', $body);
        $this->assertStringContainsString('確認画面へ進む', $body);
        $this->assertStringContainsString('name="name"', $body);
        $this->assertStringContainsString('name="email"', $body);
        $this->assertStringContainsString('name="message"', $body);
    }

    public function testInputPagePostWithModificationData(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これはテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/input', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('テスト太郎', $body);
        $this->assertStringContainsString('test@example.com', $body);
        $this->assertStringContainsString('これはテストメッセージです。', $body);
    }

    public function testConfirmPageWithValidData(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これはテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('お問い合わせ確認', $body);
        $this->assertStringContainsString('ステップ 2/3', $body);
        $this->assertStringContainsString('テスト太郎', $body);
        $this->assertStringContainsString('test@example.com', $body);
        $this->assertStringContainsString('これはテストメッセージです。', $body);
        $this->assertStringContainsString('お問い合わせを送信', $body);
        $this->assertStringContainsString('内容を修正', $body);
    }

    public function testConfirmPageWithEmptyName(): void
    {
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'message' => 'これはテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        // バリデーションエラーで入力画面に戻る
        $this->assertStringContainsString('お問い合わせ入力', $body);
        $this->assertStringContainsString('入力エラー', $body);
        $this->assertStringContainsString('お名前を入力してください。', $body);
    }

    public function testConfirmPageWithEmptyEmail(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => '',
            'message' => 'これはテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('入力エラー', $body);
        $this->assertStringContainsString('メールアドレスを入力してください。', $body);
    }

    public function testConfirmPageWithEmptyMessage(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => ''
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('入力エラー', $body);
        $this->assertStringContainsString('お問い合わせ内容を入力してください。', $body);
    }

    public function testConfirmPageWithAllEmptyFields(): void
    {
        $data = [
            'name' => '',
            'email' => '',
            'message' => ''
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('入力エラー', $body);
        $this->assertStringContainsString('お名前を入力してください。', $body);
        $this->assertStringContainsString('メールアドレスを入力してください。', $body);
        $this->assertStringContainsString('お問い合わせ内容を入力してください。', $body);
    }

    public function testCompletePagePost(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これはテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/complete', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('お問い合わせ完了', $body);
        $this->assertStringContainsString('ステップ 3/3', $body);
        $this->assertStringContainsString('お問い合わせありがとうございます！', $body);
        $this->assertStringContainsString('テスト太郎', $body);
        $this->assertStringContainsString('test@example.com', $body);
        $this->assertStringContainsString('これはテストメッセージです。', $body);
        $this->assertStringContainsString('新しいお問い合わせ', $body);
        $this->assertStringContainsString('ホームに戻る', $body);
    }

    public function testCompletePageGetRedirect(): void
    {
        $request = $this->createRequest('GET', '/form/complete');
        $response = $this->runApp($request);
        
        // GETアクセスの場合は302リダイレクト
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/form/input', $response->getHeaderLine('Location'));
    }

    public function testNonExistentRoute(): void
    {
        $request = $this->createRequest('GET', '/nonexistent');
        
        // 404エラーが発生することを期待
        $this->expectException(\Slim\Exception\HttpNotFoundException::class);
        $response = $this->runApp($request);
    }
}
