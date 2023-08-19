<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Commands\CleanExpiredConnections;
use App\Infrastructure\Commands\SendMessages;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            $connection = include 'connections.php';
            return new Settings([
                'baseUrl' => 'https://ishal.ru',
                'displayErrorDetails' => true, // Should be set to false in production
                'logError' => true,
                'logErrorDetails' => true,
                'logger' => [
                    'name' => 'monolog',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    // if true, metadata caching is forcefully disabled
                    'dev_mode' => true,

                    // path where the compiled metadata info will be cached
                    // make sure the path exists, and it is writable
                    'cache_dir' => __DIR__ . '/../var/doctrine',

                    // you should add any other path containing annotated entity classes
                    'metadata_dirs' => [__DIR__ . '/../src/Domain'],

                    'connection' => $connection['db']
                ],
                'jwt' => [
                    // The issuer name
                    'issuer' => env('JWT_ISSUER'),

                    // Max lifetime in seconds
                    'lifetime' => env('JWT_LIFETIME'),

                    // The private key
                    'private_key' => __DIR__ . env('JWT_PRIVATE'),

                    'public_key' => __DIR__ . env('JWT_PUBLIC'),
                ],
                'email' => [
                    'no-reply' => 'no-reply@friday-drop.media'
                ],
                'commands' => [
                    SendMessages::class,
                    CleanExpiredConnections::class
                ],
                'donationAlerts' => [
                    'baseUri' => 'https://www.donationalerts.com',
                    'token' => env('DONATION_ALERTS_TOKEN')
                ],
                'telegramOauth' => [
                    'url' => env('TELEGRAM_OAUTH_URL'),
                    'botId' => env('TELEGRAM_BOT_ID'),
                ]
            ]);
        }
    ]);
};
