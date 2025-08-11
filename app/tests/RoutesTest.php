<?php
namespace Tests;

class RoutesTest extends BaseTestCase
{
    public function testAllRoutesExist(): void
    {
        $routes = [
            [
                'method' => 'GET',
                'path' => '/',
                'expectedStatus' => 200,
                'description' => 'ホームページ'
            ],
            [
                'method' => 'GET',
                'path' => '/form/input',
                'expectedStatus' => 200,
                'description' => '入力画面'
            ],
            [
                'method' => 'POST',
                'path' => '/form/confirm',
                'expectedStatus' => 200,
                'description' => '確認画面（バリデーションエラーで入力画面に戻る）'
            ],
            [
                'method' => 'GET',
                'path' => '/form/complete',
                'expectedStatus' => 302,
                'description' => '完了画面（リダイレクト）'
            ],
        ];

        foreach ($routes as $route) {
            $data = [];
            if ($route['method'] === 'POST') {
                // POSTの場合は空データを送信（バリデーションエラーを期待）
                $data = ['name' => '', 'email' => '', 'message' => ''];
            }
            
            $request = $this->createRequest($route['method'], $route['path'], $data);
            $response = $this->runApp($request);
            
            $this->assertEquals(
                $route['expectedStatus'], 
                $response->getStatusCode(),
                "Route {$route['method']} {$route['path']} ({$route['description']}) returned unexpected status code"
            );
        }
    }

    public function testPostCompleteWithValidData(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これは有効なテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/complete', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostConfirmWithValidData(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これは有効なテストメッセージです。'
        ];
        
        $request = $this->createRequest('POST', '/form/confirm', $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $body = (string) $response->getBody();
        $this->assertStringContainsString('お問い合わせ確認', $body);
    }

    /**
     * @dataProvider routeDataProvider
     * @param array<string, mixed> $data
     */
    public function testSpecificRoutes(string $method, string $path, array $data, int $expectedStatus, string $description): void
    {
        $request = $this->createRequest($method, $path, $data);
        $response = $this->runApp($request);
        
        $this->assertEquals(
            $expectedStatus, 
            $response->getStatusCode(),
            "Route {$method} {$path} ({$description}) returned unexpected status code"
        );
    }

    /**
     * @return array<string, array{string, string, array<string, mixed>, int, string}>
     */
    public static function routeDataProvider(): array
    {
        return [
            'valid_form_submission' => [
                'POST', 
                '/form/confirm', 
                ['name' => 'テスト太郎', 'email' => 'test@example.com', 'message' => 'これは有効なテストメッセージです。'], 
                200, 
                '有効なデータでの確認画面'
            ],
            'invalid_form_submission' => [
                'POST', 
                '/form/confirm', 
                ['name' => '', 'email' => '', 'message' => ''], 
                200, 
                '無効なデータでの入力画面戻り'
            ],
            'valid_form_completion' => [
                'POST', 
                '/form/complete', 
                ['name' => 'テスト太郎', 'email' => 'test@example.com', 'message' => 'これは有効なテストメッセージです。'], 
                200, 
                '有効なデータでの完了画面'
            ],
            'input_modification' => [
                'POST', 
                '/form/input', 
                ['name' => 'テスト太郎', 'email' => 'test@example.com', 'message' => 'これは有効なテストメッセージです。'], 
                200, 
                '修正データでの入力画面'
            ],
        ];
    }
}
