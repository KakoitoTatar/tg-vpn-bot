<?php
declare(strict_types=1);

use App\Application\Middleware\GuidMiddleware;
use App\Application\Middleware\JwtClaimMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

return function (App $app) {
    $app->add(ContentLengthMiddleware::class);
    $app->add(GuidMiddleware::class);
    $app->add(SessionMiddleware::class);
};
