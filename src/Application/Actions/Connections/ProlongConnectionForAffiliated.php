<?php

namespace App\Application\Actions\Connections;

use App\Application\Actions\Action;
use App\Application\DTO\ConnectionDTO;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\User\User;
use DateInterval;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class ProlongConnectionForAffiliated extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param RateRepositoryInterface $rateRepository
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ClientRepositoryInterface $clientRepository
     */
    public function __construct(
        LoggerInterface                                $logger,
        Serializer                                     $serializer,
        ValidatorInterface                             $validator,
        private readonly ConnectionRepositoryInterface $connectionRepository,
        private readonly ClientRepositoryInterface     $clientRepository
    )
    {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        /** @var Client $client */
        $client = $this->request->getAttribute('client');
        $connection = $this->connectionRepository->find((int)$this->request->getParsedBody()['id']);


        $periodEnd = clone $connection->getPeriodEnd();
        $periodEnd = $periodEnd->add(DateInterval::createFromDateString($client->getFreeDays() . ' day'));
        $connection->setPeriodEnd($periodEnd);
        $this->connectionRepository->update($connection);
        $client->setFreeDays(0);
        $this->clientRepository->update($client);

        $vpnService = VpnServiceFactory::getControlService($connection->getInstance()->getProtocol());
        $vpnService->authenticate($connection->getInstance()->getConnection());
        $vpnClient = $vpnService->getUser(['id' => $connection->getVpnKey()]);

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

    /**
     * @return array
     */
    protected function getRules(): array
    {
        return ['id' => ['required']];
    }
}