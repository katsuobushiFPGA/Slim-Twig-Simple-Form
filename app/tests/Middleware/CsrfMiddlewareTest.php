<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Tests\BaseTestCase;
use Slim\Csrf\Guard;

class CsrfMiddlewareTest extends BaseTestCase
{
    public function testCsrfTokenIsRequiredForPostRequests(): void
    {
        // CSRFトークンなしでPOSTリクエストを送信
        $request = $this->createRequest('POST', '/form/confirm');
        $request = $request->withParsedBody([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'message' => 'テストメッセージ'
        ]);

        $response = $this->app->handle($request);

        // CSRF検証エラーで400が返されることを確認（Slim CSRFは400を返す）
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testValidCsrfTokenAllowsPostRequests(): void
    {
        // まずトークンを生成（Guardはリクエスト処理前はkeyPair未生成のため明示的に生成）
        $pair = $this->csrfGuard->generateToken();
        $nameKey = $this->csrfGuard->getTokenNameKey();
        $valueKey = $this->csrfGuard->getTokenValueKey();

        // 有効なCSRFトークン付きでPOSTリクエスト送信
        $postRequest = $this->createRequest('POST', '/form/confirm', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'message' => 'テストメッセージ',
            $nameKey => $pair[$nameKey],
            $valueKey => $pair[$valueKey],
        ]);

        $response = $this->runApp($postRequest);

        // 正常に処理されることを確認（200または302、400以外）
        $this->assertNotEquals(400, $response->getStatusCode());
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function testCsrfTokenInFormTemplate(): void
    {
        $request = $this->createRequest('GET', '/form/input');
        $response = $this->runApp($request);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string) $response->getBody();

        // CSRFトークンのhidden inputフィールドが含まれていることを確認
        $this->assertStringContainsString('type="hidden"', $body);
        // name属性（固定文字列）と value（生成された値）が含まれていること
        $this->assertStringContainsString('name="' . $this->csrfGuard->getTokenNameKey() . '"', $body);
        $this->assertStringContainsString('name="' . $this->csrfGuard->getTokenValueKey() . '"', $body);
    }
}
