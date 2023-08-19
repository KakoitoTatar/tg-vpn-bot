<?php

namespace App\Application\Middleware;

use App\Application\Helpers\Guid;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GuidMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $guid = $request->getCookieParams()['guid'] ?? null;
        if ($guid !== null) {
            return $handler->handle($request);
        }
        $guid = Guid::v4();

        $cookies = $request->getCookieParams();
        $cookies['guid'] = $guid;
        $request = $request->withCookieParams($cookies);

        setcookie(name: 'guid', value: $guid);

        return $handler->handle($request);
    }
}