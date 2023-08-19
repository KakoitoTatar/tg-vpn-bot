<?php

namespace App\Application\Actions\Connections;

use App\Application\Actions\Action;
use App\Application\DTO\ConnectionDTO;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Connection\Connection;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class GetConnectionsAction extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaymentRepositoryInterface $paymentRepository
     */
    public function __construct(
        LoggerInterface                             $logger,
        Serializer                                  $serializer,
        ValidatorInterface                          $validator,
        private readonly PaymentRepositoryInterface $paymentRepository
    )
    {
        parent::__construct($logger, $serializer, $validator);
    }

    protected function action(): Response
    {
        /** @var Client $client */
        $client = $this->request->getAttribute('client');

        $connections = $client->getConnections()->toArray();

        $connections = array_map(function (Connection $connection) {
            if (!$connection->isActive()) {
                return [];
            }

            if ($connection->isActive()) {
                $vpnControlService = VpnServiceFactory::getControlService($connection->getInstance()->getProtocol());

                $vpnControlService->authenticate($connection->getInstance()->getConnection());

                $vpnClient = $vpnControlService->getUser(['id' => $connection->getVpnKey()]);
                $key = $vpnClient->getConnection()['accessUrl'];
            }

            return [
                'name' => $connection->getRate()->getName(),
                'activeTo' => $connection->getPeriodEnd(),
                'active' => $connection->isActive(),
                'key' => $key,
                'id' => $connection->getId(),
            ];
        }, $connections);

        $connections = array_filter($connections);


        return $this->respondWithData(ConnectionDTO::createFromCollection($connections));
    }


    protected function getAcceptedRoles(): array
    {
        return [User::USER];
    }

    protected function getRules(): array
    {
        return [];
    }
}