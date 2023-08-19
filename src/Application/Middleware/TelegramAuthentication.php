<?php

namespace App\Application\Middleware;

use App\Application\Helpers\Coupon;
use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Promocode\Promocode;
use App\Domain\Promocode\PromocodeRepositoryInterface;
use App\Domain\Promocode\PromocodeTypes;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TelegramAuthentication implements MiddlewareInterface
{
    /**
     * @param ClientRepositoryInterface $clientRepository
     * @param PromocodeRepositoryInterface $promocodeRepository
     */
    public function __construct(
        protected readonly ClientRepositoryInterface $clientRepository,
        protected PromocodeRepositoryInterface $promocodeRepository
    ){}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $user = [];
        if (isset($body['message'])) {
            $user = $body['message']['from'];
        } elseif (isset($body['callback_query'])) {
            $user = $body['callback_query']['from'];
        }
        $client = $this->clientRepository->find($user['id']);

        if ($client === null) {
            $client = new Client();
            $client->setId($user['id']);
            $client->setFreeDays(0);
            $this->clientRepository->save($client);

            $referralPromocode = new Promocode();
            $referralPromocode->setActive(true);
            $referralPromocode->setOwner($client);
            $referralPromocode->setType(PromocodeTypes::Referral);
            $referralPromocode->setMultipleUse(false);
            $referralPromocode->setName(Coupon::generate());
            $this->promocodeRepository->save($referralPromocode);

            $client->setAffiliatedPromocode($referralPromocode);
            $this->clientRepository->update($client);
        }

        $client->setName($user['first_name']);
        $client->setUsername($user['username']);

        $request = $request->withAttribute('client', $client);

        return $handler->handle($request);
    }
}