<?php

namespace App\Application\Actions\Payments;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\Promocode\PromocodeTypes;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

class ServePayment extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaymentRepositoryInterface $paymentRepository
     * @param ConnectionRepositoryInterface $connectionRepository
     * @param ClientRepositoryInterface $clientRepository
     */
    public function __construct(
        LoggerInterface                                $logger,
        Serializer                                     $serializer,
        ValidatorInterface                             $validator,
        private readonly PaymentRepositoryInterface    $paymentRepository,
        private readonly ConnectionRepositoryInterface $connectionRepository,
        private readonly ClientRepositoryInterface     $clientRepository
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
        $requestData = $this->request->getParsedBody();
        $payment = $this->paymentRepository->findOneBy(['paymentSystemId' => $requestData['paymentSystemId']]);
        $expiredPaymentsTime = $requestData['expiredPaymentTime'];
        if ($payment && $requestData['actualSum'] >= $payment->getExpectedSum()
            && $payment->getStatus() !== PaymentStatus::Serviced) {
            $payment->setStatus(PaymentStatus::Serviced);
            $payment->setUpdatedAt(new \DateTime());
            $payment->setActualSum($requestData['actualSum']);

            $client = $payment->getClient();
            $connection = $payment->getConnection();

            $duration = $connection->getRate()->getDuration();

            if ($payment->getPromocode() !== null) {
                $duration += $payment->getPromocode()->getType()->getAdditionalDays();
            }

            if (
                $payment->getPromocode() !== null
                && $payment->getPromocode()->getType() === PromocodeTypes::Referral
            ) {
                $owner = $payment->getPromocode()->getOwner();
                $owner->setFreeDays($owner->getFreeDays() + 5);
                $this->clientRepository->update($owner);
            }

            if ($connection->getPeriodEnd() === null) {
                $connection->setPeriodStart(new \DateTime());
                $connection->setPeriodEnd(new \DateTime('+' . $duration . ' days'));
            } else {
                $periodEnd = new \DateTime($connection->getPeriodEnd()->format('d.m.Y'));
                $periodEnd->modify('+' . $duration . 'days');
                $connection->setPeriodEnd($periodEnd);
            }

            $connection->setActive(true);

            $this->clientRepository->update($client);
            $this->connectionRepository->update($connection);
            $this->paymentRepository->update($payment);

            return $this->respondWithData([
                'status' => 'Activated',
                'completedAt' => new \DateTime(),
                'createdAt' => $payment->getCreatedAt()->format('Y-m-d'),
                'paymentId' => $payment->getPaymentSystemId(),
                'expectedAmount' => $payment->getConnection()->getRate()->getPrice(),
                'actualAmount' => $payment->getActualSum(),
                'clientId' => $payment->getClient()->getId()
            ], 201);
        } elseif ($expiredPaymentsTime > $payment->getCreatedAt()) {
            $payment->setStatus(PaymentStatus::Failed);
            $payment->setUpdatedAt(new \DateTime());
            $this->paymentRepository->update($payment);
            $this->logger->info(
                'Payment failed',
                [
                    'completedAt' => new \DateTime(),
                    'paymentId' => $payment->getPaymentSystemId()
                ]
            );
            return $this->respondWithData(
                [
                    'status' => 'Failed',
                    'completedAt' => new \DateTime(),
                    'paymentId' => $payment->getPaymentSystemId()

                ],
                400);
        }

        return $this->respondWithData([], 200);
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
            'actualSum' => ['required', 'integer'],
            'paymentSystemId' => ['required'],
            'expiredPaymentTime' => ['required', ['dateFormat', 'd-m-Y-H-i-s']]
        ];
    }
}