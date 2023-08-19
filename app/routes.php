<?php
declare(strict_types=1);

use App\Application\Actions\Client\GetClient;
use App\Application\Actions\Connections\CreateConnectionForAffiliatedProgram;
use App\Application\Actions\Connections\GetConnection;
use App\Application\Actions\Connections\GetConnectionsAction;
use App\Application\Actions\Connections\ProlongConnectionForAffiliated;
use App\Application\Actions\Payments\CancelPayment;
use App\Application\Actions\Payments\CreatePayment;
use App\Application\Actions\Payments\GetPayment;
use App\Application\Actions\Payments\ServePayment;
use App\Application\Actions\Rates\GetRateAction;
use App\Application\Actions\Rates\GetRatesAction;
use App\Application\Actions\Telegram\TelegramAction;
use App\Application\Middleware\JwtClaimMiddleware;
use App\Application\Middleware\NutgramClaimMiddleware;
use App\Application\Middleware\TelegramAuthentication;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;


return function (App $app) {
    $app->group('/api', function ($app) {
        $app->get('/client', GetClient::class)->add(JwtClaimMiddleware::class);
        $app->get('/rates', GetRatesAction::class);
        $app->get('/rates/{id}', GetRateAction::class);
        $app->post('/connections/affiliated', CreateConnectionForAffiliatedProgram::class)->add(JwtClaimMiddleware::class);
        $app->patch('/connections/affiliated/{id}', ProlongConnectionForAffiliated::class)->add(JwtClaimMiddleware::class);
        $app->get('/connections', GetConnectionsAction::class)->add(JwtClaimMiddleware::class);
        $app->get('/connections/{id}', GetConnection::class)->add(JwtClaimMiddleware::class);
        $app->post('/payment', CreatePayment::class)->add(JwtClaimMiddleware::class);
        $app->post('/payment/serve', ServePayment::class)->add(JwtClaimMiddleware::class);
        $app->get('/payment/{id}', GetPayment::class)->add(JwtClaimMiddleware::class);
        $app->delete('/payment/{id}', CancelPayment::class)->add(JwtClaimMiddleware::class);
    });

    $app->post('/tg/' . env('TG_API_KEY') .'/handle', TelegramAction::class)
        ->add(NutgramClaimMiddleware::class)
        ->add(TelegramAuthentication::class);

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
