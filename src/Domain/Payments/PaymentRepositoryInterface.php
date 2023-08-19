<?php

namespace App\Domain\Payments;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

interface PaymentRepositoryInterface
{
    /**
     * @param int $id
     * @return Payment
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Collection
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Payment
     */
    public function findOneBy(array $conditions);

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function save(Payment $payment): Payment;

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function update(Payment $payment): Payment;

    /**
     * @param Payment $payment
     * @return bool
     */
    public function delete(Payment $payment): bool;

    /**
     * @param Criteria $criteria
     * @return mixed
     */
    public function matching(Criteria $criteria);
}