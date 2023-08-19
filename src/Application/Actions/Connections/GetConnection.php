<?php

namespace App\Application\Actions\Connections;

use App\Application\Actions\Action;
use App\Application\DTO\ConnectionDTO;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;
use Symfony\Component\Serializer\Serializer;

class GetConnection extends Action
{
    public function __construct(
        LoggerInterface                                $logger,
        Serializer                                     $serializer,
        ValidatorInterface                             $validator,
        private readonly ConnectionRepositoryInterface $connectionRepository
    )
    {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        $client = $this->request->getAttribute('client');

        $connection = $this->connectionRepository->find($this->request->getParsedBody()['id']);

        if ($connection->getClient()->getId() !== $client->getId() || $connection->isActive() === false) {
            throw new HttpForbiddenException($this->request);
        }

        $vpnControlService = VpnServiceFactory::getControlService($connection->getInstance()->getProtocol());

        $vpnControlService->authenticate($connection->getInstance()->getConnection());

        $vpnClient = $vpnControlService->getUser(['id' => $connection->getVpnKey()]);

        return $this->respondWithData([
            'id' => $connection->getId(),
            'name' => $connection->getRate()->getName(),
            'rateId' => $connection->getRate()->getId(),
            'activeTo' => $connection->getPeriodEnd()->format('d.m.Y'),
            'key' => $vpnClient->getConnection()['accessUrl']
        ]);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [User::USER];
    }

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'id' => ['required', 'integer']
        ];
    }
}