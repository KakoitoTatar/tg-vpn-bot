<?php

namespace App\Application\Actions\Connections;

use App\Application\Actions\Action;
use App\Application\DTO\ConnectionDTO;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\Connection;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Instance\Instance;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\User\User;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpForbiddenException;
use Symfony\Component\Serializer\Serializer;

class CreateConnectionForAffiliatedProgram extends Action
{
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly RateRepositoryInterface $rateRepository,
        private readonly ConnectionRepositoryInterface $connectionRepository,
        private readonly ClientRepositoryInterface $clientRepository
    )
    {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    protected function action(): Response
    {
        /** @var Client $client */
        $client = $this->request->getAttribute('client');

        if ($client->getFreeDays() === 0) {
            throw new HttpForbiddenException($this->request);
        }

        $rate = $this->rateRepository->find(1);

        $connection = new Connection();
        $connection->setActive(true);
        $connection->setClient($client);
        $connection->setRate($rate);
        $connection->setPeriodStart(new DateTime());
        $connection->setPeriodEnd(new DateTime('+' . $client->getFreeDays() . ' days'));
        $this->connectionRepository->save($connection);

        $emptiestInstance = null;
        $emptiestInstanceAmount = null;

        /** @var Instance $instance */
        foreach ($connection->getRate()->getInstances() as $instance) {
            if ($emptiestInstanceAmount === null) {
                $emptiestInstanceAmount = $instance->getConnnections()->count();
                $emptiestInstance = $instance;
            } elseif ($emptiestInstanceAmount > $instance->getConnnections()->count()) {
                $emptiestInstanceAmount = $instance->getConnnections()->count();
                $emptiestInstance = $instance;
            }
        }

        $connection->setInstance($emptiestInstance);

        $vpnService = VpnServiceFactory::getControlService($emptiestInstance->getProtocol());
        $vpnService->authenticate($emptiestInstance->getConnection());
        $vpnUser = $vpnService->createUser(['name' => $connection->getId()]);

        $connection->setVpnKey($vpnUser->getId());

        $this->connectionRepository->update($connection);

        $client->setFreeDays(0);
        $this->clientRepository->update($client);

        $vpnControlService = VpnServiceFactory::getControlService($connection->getInstance()->getProtocol());

        $vpnControlService->authenticate($connection->getInstance()->getConnection());

        $vpnClient = $vpnControlService->getUser(['id' => $connection->getVpnKey()]);

        return $this->respondWithData(ConnectionDTO::create(
            $connection->getId(),
            $connection->getRate()->getName(),
            $connection->getPeriodEnd(),
            $vpnClient->getConnection()['accessUrl']
        ));
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [User::USER];
    }

    protected function getRules(): array
    {
        return [];
    }
}