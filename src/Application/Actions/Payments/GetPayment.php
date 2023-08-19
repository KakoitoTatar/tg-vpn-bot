<?php

namespace App\Application\Actions\Payments;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\User\User;
use Doctrine\Common\Collections\Criteria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Serializer\Serializer;

class GetPayment extends Action
{
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
        $client = $this->request->getAttribute('client');

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->eq('id', $this->request->getParsedBody()['id']));
        $criteria->andWhere(Criteria::expr()->in('status', [PaymentStatus::Awaiting, PaymentStatus::Paid]));
        $payments = $this->paymentRepository->matching($criteria)->toArray();

        if ($payments === []) {
            throw new HttpNotFoundException($this->request, 'Payments not found');
        }

        /** @var Payment $payment */
        $payment = $payments[0];

        return $this->respondWithData([
            'id' => $payment->getId(),
            'paymentCode' => $payment->getPaymentSystemId(),
            'sum' => $payment->getExpectedSum(),
            'status' => $payment->getStatus()->value,
            'connection' => $payment->getConnection()->isActive() ? $payment->getConnection()->getId() : null
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
        return [];
    }
}