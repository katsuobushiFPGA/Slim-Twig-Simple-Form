<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class FormController
{
    public function index(Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'index.html.twig', [
            'title' => 'Slim 4 + Twig Simple Form',
            'message' => 'Welcome to Slim Framework with Twig!'
        ]); 
    }

    public function form(Request $request, Response $response)
    {
        $view = Twig::fromRequest($request);
        $data = [];
        // POSTリクエストを処理するためのメソッド
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
        }

        return $view->render($response, 'form.html.twig', [
            'title' => 'Simple Form',
            'data' => $data
        ]);
    }
}