<?php

namespace App\Application\Actions\Payments;

use App\Application\Actions\Action;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Payments\PaymentStatus;
use App\Domain\User\User;
use Doctrine\Common\Collections\Criteria;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Symfony\Component\Serializer\Serializer;

class CancelPayment extends Action
{
    /**
     * @param LoggerInterface $logger
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     * @param PaymentRepositoryInterface $paymentRepository
     * @param ConnectionRepositoryInterface $connectionRepository
     */
    public function __construct(
        LoggerInterface $logger,
        Serializer $serializer,
        ValidatorInterface $validator,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ConnectionRepositoryInterface $connectionRepository
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
        $criteria->andWhere(Criteria::expr()->in('status', [PaymentStatus::Awaiting]));
        $payments = $this->paymentRepository->matching($criteria)->toArray();

        if ($payments === []) {
            throw new HttpNotFoundException($this->request, 'Payments not found');
        }

        /** @var Payment $payment */
        $payment = $payments[0];

        $connection = $payment->getConnection();
        $payment->setConnection(null);
        $payment->setStatus(PaymentStatus::Failed);
        $payment->setReason($this->request->getParsedBody()['reason']);
        $this->paymentRepository->update($payment);
        $this->connectionRepository->delete($connection);

        return $this->respondWithData(['status' => 'success']);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [User::USER];
    }

    /**
     * @return array[]
     */
    protected function getRules(): array
    {
        return [
            'reason' => ['required']
        ];
    }
}