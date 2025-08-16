<?php

declare(strict_types=1);

namespace App\Error;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as Psr7Response;
use Slim\Views\Twig;
use Throwable;

class GeneralErrorRenderer
{
    public function __construct(private Twig $twig, private bool $displayErrorDetails = false)
    {
    }

    /**
     * @param list<string> $details
     */
    private function render(Response $response, int $status, string $title, string $message, array $details = [], bool $json = false): Response
    {
        $tpl = $json ? 'error/error.json.twig' : 'error/error.html.twig';

        return $this->twig->render($response->withStatus($status), $tpl, [
            'status_code' => $status,
            'status_text' => $title,
            'message' => $message,
            'details' => $details,
        ]);
    }

    public function __invoke(Request $request, Throwable $exception, bool $displayErrorDetails): Response
    {
        $accept = $request->getHeaderLine('Accept');
        $wantsJson = str_contains($accept, 'application/json');
        $status = 500;
        $title = 'Internal Server Error';
        $message = $this->displayErrorDetails ? $exception->getMessage() : '予期しないエラーが発生しました。';
        $details = [];
        if ($this->displayErrorDetails) {
            $details[] = $exception::class;
            $details[] = $exception->getFile() . ':' . $exception->getLine();
        }
        $response = new Psr7Response();

        return $this->render($response, $status, $title, $message, $details, $wantsJson);
    }
}
