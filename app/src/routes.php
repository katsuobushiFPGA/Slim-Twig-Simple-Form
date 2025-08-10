<?php
use Slim\App;
use App\Controllers\FormController;

return function (App $app) {
    $app->get('/', [FormController::class, 'index']);
    $app->get('/form', [FormController::class, 'form']);
    $app->post('/form', [FormController::class, 'form']);
};