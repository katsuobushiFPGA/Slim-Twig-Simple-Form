<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Validators\ContactFormValidator;

class FormController
{
    public function index(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'index.html.twig', [
            'title' => 'お問い合わせフォーム - Slim 4 + Twig',
            'message' => 'Welcome to Slim Framework with Twig!'
        ]); 
    }

    public function input(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $data = [];
        
        // POSTリクエストの場合は修正データを受け取る
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
        }
        
        return $view->render($response, 'form/input.html.twig', [
            'title' => 'お問い合わせ入力 - お問い合わせフォーム',
            'data' => $data
        ]);
    }

    public function confirm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        $data = $request->getParsedBody();
        
        // データが配列でない場合は空配列で初期化
        if (!is_array($data)) {
            $data = [];
        }
        
        // バリデーション
        $validator = new ContactFormValidator();
        $errors = $validator->validate($data);
        
        // トリム済みデータを取得
        $data = $validator->getTrimmedData($data);
        
        if (!empty($errors)) {
            return $view->render($response, 'form/input.html.twig', [
                'title' => 'お問い合わせ入力 - お問い合わせフォーム',
                'errors' => $errors,
                'data' => $data
            ]);
        }
        
        return $view->render($response, 'form/confirm.html.twig', [
            'title' => 'お問い合わせ確認 - お問い合わせフォーム',
            'data' => $data
        ]);
    }

    public function complete(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            
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