<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

class NutgramClaimMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $token = explode('/', $request->getRequestTarget())[2];
        $botId = (int)explode(':', $token)[0];
        $this->container->set(
            Nutgram::class,
            function (ContainerInterface $c) use ($token) {
                $nutgram = new \App\Application\Services\Nutgram\Nutgram(
                    $token,
                    [
                        'logger' => $c->get(LoggerInterface::class),
                        'cache' => $c->get(CacheInterface::class),
                        'container' => $c,
                    ]
                );
                $nutgram->setRunningMode(Webhook::class);
                return $nutgram;
            }
        );

        $this->container->set(
            \SergiX44\Nutgram\Cache\ConversationCache::class,
            function (ContainerInterface $c) use ($botId) {
                return new \App\Application\Services\Nutgram\ConversationCache(
                    $c->get(CacheInterface::class),
                    $botId
                );
            }
        );

        return $handler->handle($request);
    }
}