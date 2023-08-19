<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Application\Auth\JwtAuth;
use Slim\Exception\HttpForbiddenException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JwtClaimMiddleware implements MiddlewareInterface
{
    /**
     * @var JwtAuth
     */
    private JwtAuth $jwtAuth;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @param JwtAuth $jwtAuth The JWT auth
     */
    public function __construct(
        JwtAuth $jwtAuth,
        private readonly ClientRepositoryInterface $clientRepository
    ) {
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ExceptionInterface
     */
    public function process(
        ServerRequestInterface  $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $credentials = $request->getHeader('Authorization')[0] ?? null;

        if (!$credentials) {
            throw new HttpForbiddenException($request, 'Action is forbidden');
        }

        $credentials = explode(' ', $credentials)[1];

        if (!$this->jwtAuth->validateToken($credentials)) {
            throw new HttpForbiddenException($request, 'Action is forbidden');
        }

        // Append valid token
        $parsedToken = $this->jwtAuth->createParsedToken($credentials);

        $client = $this->clientRepository->find($parsedToken->claims()->all()['id']);

        $request = $request->withAttribute('client', $client);

        return $handler->handle($request);
    }
}