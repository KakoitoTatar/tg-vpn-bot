<?php

namespace App\Application\Actions\Client;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class GetClient extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaymentRepositoryInterface $paymentRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly PaymentRepositoryInterface $paymentRepository
    ) {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     */
    protected function action(): Response
    {
        /** @var Client $client */
        $client = $this->request->getAttribute('client');

        $payments = $this->paymentRepository->findBy(['status' => PaymentStatus::Serviced, 'client' => $client]);
        return $this->respondWithData([
            'id' => $client->getId(),
            'isNewClient' => $payments === [],
            'freeDays' => $client->getFreeDays(),
            'referralCode' => $client->getAffiliatedPromocode()->getName()
        ]);
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