<?php

namespace App\Application\Actions\Payments;

use App\Application\Actions\Action;
use App\Application\Helpers\StringHelper;
use App\Application\Services\VpnControlService\VpnServiceFactory;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\Client;
use App\Domain\Connection\Connection;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Instance\Instance;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\Promocode\PromocodeRepositoryInterface;
use App\Domain\Promocode\PromocodeTypes;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class CreatePayment extends Action
{
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly ConnectionRepositoryInterface $connectionRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly RateRepositoryInterface $rateRepository,
        private readonly PromocodeRepositoryInterface $promocodeRepository
    ) {
        parent::__construct($logger, $serializer, $validator);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    protected function action(): Response
    {
        $requestData = $this->request->getParsedBody();
        /** @var Client $client */
        $client = $this->request->getAttribute('client', null);

        $rate = $this->rateRepository->find($requestData['rateId']);

        $promocode = null;
        if ($requestData['promocode'] ?? null) {
            $promocode = $this->promocodeRepository->findOneBy(['name' => $requestData['promocode']]);

            if ($promocode === null
                || $promocode->getClient() !== null && $promocode->getClient() !== $client
                || $promocode->getType() === PromocodeTypes::Referral && $promocode->getOwner() === $client
            ) {
                return $this->respondWithData(['message' => 'Promocode doesn\'t exist'], 404);
            }

            $previousPaymentsWithCurrentPromocode = $this->paymentRepository
                ->findOneBy(['client' => $client, 'promocode' => $promocode, 'status' => PaymentStatus::Serviced]);

            if (!$promocode->isMultipleUse() && $previousPaymentsWithCurrentPromocode !== null) {
                return $this->respondWithData(['message' => 'Promocode already used'], 410);
            }
        }

        if ($requestData['connectionId'] ?? null) {
            $connection = $this->connectionRepository->find($requestData['connectionId']);
        } else {
            $connection = new Connection();
            $connection->setActive(false);
            $connection->setClient($client);
            $connection->setRate($rate);
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
        }

        $payment = new Payment();
        $payment->setPaymentSystemId(StringHelper::generateRandomString(24));
        $payment->setConnection($connection);
        $payment->setStatus(PaymentStatus::Awaiting);
        $payment->setCreatedAt(new \DateTime());
        $payment->setUpdatedAt(new \DateTime());
        $payment->setExpectedSum($rate->getPrice());

        if ($promocode !== null) {
            $payment->setPromocode($promocode);

            $discountedPrice = ceil($rate->getPrice() * ((100 - $promocode->getType()->getDiscount()) / 100));
            $payment->setExpectedSum($discountedPrice);
        }

        $payment->setClient($client);
        $this->paymentRepository->save($payment);

        return $this->respondWithData([
            'id' => $payment->getId(),
            'paymentCode' => $payment->getPaymentSystemId(),
            'sum' => $payment->getExpectedSum(),
            'status' => $payment->getStatus()->value,
            'connection' => $payment->getConnection()->isActive() ? $payment->getConnection()->getId() : null
        ]);
    }

    protected function getAcceptedRoles(): array
    {
        return [User::USER];
    }

    protected function getRules(): array
    {
        return [
            'rateId' => ['required', 'integer'],
            'promocode' => [],
            'connectionId' => ['integer']
        ];
    }
}