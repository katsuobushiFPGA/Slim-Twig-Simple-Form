<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repository\ContactRepository;
use App\Validators\ContactFormValidator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Slim\Views\Twig;

class FormController
{
    public function __construct(
        private ContactRepository $contactRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * CSRFトークンをリクエストデータから除去する.
     *
     * @param mixed $data リクエストデータ（配列でない場合は空配列として扱う）
     * @return array<string,mixed> CSRFトークンを除去したデータ
     */
    private function filterCsrfTokens(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }
        unset($data['csrf_name'], $data['csrf_value']);

        return $data;
    }

    /**
     * ホームページの表示.
     */
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        return $view->render($response, 'index.html.twig', [
            'title' => 'お問い合わせフォーム - Slim 4 + Twig',
            'message' => 'Welcome to Slim Framework with Twig!',
        ]);
    }

    /**
     * フォーム入力画面の表示.
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
            ],
        ]);
    }

    /**
     * フォーム確認画面の表示・バリデーション処理.
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
                ],
            ]);
        }

        return $view->render($response, 'form/confirm.html.twig', [
            'title' => 'お問い合わせ確認 - お問い合わせフォーム',
            'data' => $data,
            'csrf' => [
                'tokenName' => $tokenName,
                'tokenValue' => $tokenValue,
            ],
        ]);
    }

    /**
     * フォーム送信完了処理.
     */
    public function complete(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);

        if ($request->getMethod() === 'POST') {
            $rawData = $request->getParsedBody();
            $data = $this->filterCsrfTokens($rawData);

            // バリデーション（確認画面をスキップした直接送信への対策）
            $validator = new ContactFormValidator();
            $errors = $validator->validate($data);
            $data = $validator->getTrimmedData($data);

            if (!empty($errors)) {
                // エラーがある場合は入力画面にリダイレクト
                $tokenName = (string) $request->getAttribute('csrf_name', '');
                $tokenValue = (string) $request->getAttribute('csrf_value', '');

                return $view->render($response, 'form/input.html.twig', [
                    'title' => 'お問い合わせ入力 - お問い合わせフォーム',
                    'errors' => $errors,
                    'data' => $data,
                    'csrf' => [
                        'tokenName' => $tokenName,
                        'tokenValue' => $tokenValue,
                    ],
                ]);
            }

            try {
                // データベースに保存
                $requestId = (string) $request->getAttribute('request_id', '');
                $contactData = [
                    'name' => $data['name'] ?? '',
                    'email' => $data['email'] ?? '',
                    'message' => $data['message'] ?? '',
                    'request_id' => $requestId,
                ];

                $savedId = $this->contactRepository->save($contactData);

                // 成功ログ
                $this->logger->info('Contact form submitted successfully', [
                    'contact_id' => $savedId,
                    'request_id' => $requestId,
                    'name' => $contactData['name'],
                    'email' => $contactData['email'],
                ]);

                return $view->render($response, 'form/complete.html.twig', [
                    'title' => 'お問い合わせ完了 - お問い合わせフォーム',
                    'data' => $data,
                    'contact_id' => $savedId,
                ]);
            } catch (RuntimeException $e) {
                // データベースエラーの場合
                $this->logger->error('Failed to save contact form', [
                    'request_id' => $request->getAttribute('request_id', ''),
                    'error' => $e->getMessage(),
                    'data' => $data,
                ]);

                // エラーページまたはフォームにエラーメッセージを表示
                return $view->render($response->withStatus(500), 'form/input.html.twig', [
                    'title' => 'お問い合わせ入力 - お問い合わせフォーム',
                    'errors' => ['システムエラーが発生しました。しばらくしてから再度お試しください。'],
                    'data' => $data,
                    'csrf' => [
                        'tokenName' => (string) $request->getAttribute('csrf_name', ''),
                        'tokenValue' => (string) $request->getAttribute('csrf_value', ''),
                    ],
                ]);
            }
        } else {
            // GETアクセスの場合はフォーム入力ページにリダイレクト
            return $response->withHeader('Location', '/form/input')->withStatus(302);
        }
    }
}
