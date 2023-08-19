<?php

namespace App\Infrastructure\Doctrine\Payment;

use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentRepositoryInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository implements PaymentRepositoryInterface
{
    /**
     * @param Payment $payment
     * @return Payment
     */
    public function save(Payment $payment): Payment
    {
        $this->getEntityManager()->persist($payment);
        $this->getEntityManager()->flush();
        return $payment;
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function update(Payment $payment): Payment
    {
        $this->getEntityManager()->flush();

        return $payment;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    public function delete(Payment $payment): bool
    {
        $this->getEntityManager()->remove($payment);
        $this->getEntityManager()->flush();

        return true;
    }
}