<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Validators\ContactFormValidator;

class FormController
{
    /**
     * CSRFトークンをリクエストデータから除去する
     * 
     * @param array<string,mixed> $data リクエストデータ
     * @return array<string,mixed> CSRFトークンを除去したデータ
     */
    private function filterCsrfTokens(array $data): array
    {
        // CSRFトークンキーを除去（Slim CSRFライブラリのデフォルトキー）
        unset($data['csrf_name'], $data['csrf_value']);
        return $data;
    }

    /**
     * ホームページの表示
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'index.html.twig', [
            'title' => 'お問い合わせフォーム - Slim 4 + Twig',
            'message' => 'Welcome to Slim Framework with Twig!'
        ]);
    }

    /**
     * フォーム入力画面の表示
     */
    public function input(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $data = [];

        // POSTリクエストの場合は修正データを受け取る
        if ($request->getMethod() === 'POST') {
            $rawData = $request->getParsedBody();
            $data = $this->filterCsrfTokens($rawData);
        }

        // CSRFトークン（Guardミドルウェアが付与したリクエスト属性）
        $tokenName = (string) $request->getAttribute('csrf_name', '');
        $tokenValue = (string) $request->getAttribute('csrf_value', '');

        return $view->render($response, 'form/input.html.twig', [
            'title' => 'お問い合わせ入力 - お問い合わせフォーム',
            'data' => $data,
            'csrf' => [
                'tokenName' => $tokenName,
                'tokenValue' => $tokenValue,
            ]
        ]);
    }

    /**
     * フォーム確認画面の表示・バリデーション処理
     */
    public function confirm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $rawData = $request->getParsedBody();

        // データが配列でない場合は空配列で初期化
        if (!is_array($rawData)) {
            $rawData = [];
        }

        // CSRFトークンを除去したデータを取得
        $data = $this->filterCsrfTokens($rawData);

        // バリデーション
        $validator = new ContactFormValidator();
        $errors = $validator->validate($data);

        // トリム済みデータを取得
        $data = $validator->getTrimmedData($data);

        // CSRFトークン（Guardミドルウェアが付与したリクエスト属性）
        $tokenName = (string) $request->getAttribute('csrf_name', '');
        $tokenValue = (string) $request->getAttribute('csrf_value', '');

        if (!empty($errors)) {
            return $view->render($response, 'form/input.html.twig', [
                'title' => 'お問い合わせ入力 - お問い合わせフォーム',
                'errors' => $errors,
                'data' => $data,
                'csrf' => [
                    'tokenName' => $tokenName,
                    'tokenValue' => $tokenValue,
                ]
            ]);
        }

        return $view->render($response, 'form/confirm.html.twig', [
            'title' => 'お問い合わせ確認 - お問い合わせフォーム',
            'data' => $data,
            'csrf' => [
                'tokenName' => $tokenName,
                'tokenValue' => $tokenValue,
            ]
        ]);
    }

    /**
     * フォーム送信完了処理
     */
    public function complete(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        if ($request->getMethod() === 'POST') {
            $rawData = $request->getParsedBody();

            // CSRFトークンを除去したデータを取得
            $data = $this->filterCsrfTokens($rawData);

            // ここで実際の処理（メール送信など）を行う
            // 今回は省略

            return $view->render($response, 'form/complete.html.twig', [
                'title' => 'お問い合わせ完了 - お問い合わせフォーム',
                'data' => $data
            ]);
        } else {
            // GETアクセスの場合はフォーム入力ページにリダイレクト
            return $response->withHeader('Location', '/form/input')->withStatus(302);
        }
    }
}
