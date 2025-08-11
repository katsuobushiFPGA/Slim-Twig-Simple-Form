<?php
use Slim\App;
use App\Controllers\FormController;

return function (App $app) {
    $app->get('/', [FormController::class, 'index']);
    $app->get('/form/input', [FormController::class, 'input']);
    $app->post('/form/input', [FormController::class, 'input']);
    $app->post('/form/confirm', [FormController::class, 'confirm']);
    $app->post('/form/complete', [FormController::class, 'complete']);
    $app->get('/form/complete', [FormController::class, 'complete']);
};