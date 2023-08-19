<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Rate;

use App\Domain\DomainException\UniqueConstraintViolationException;
use App\Domain\Rate\Rate;
use App\Domain\Rate\RateRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class RateRepository extends EntityRepository implements RateRepositoryInterface
{
    /**
     * @param Rate $rate
     * @return Rate
     */
    public function save(Rate $rate): Rate
    {
        $this->getEntityManager()->persist($rate);
            $this->getEntityManager()->flush();

        return $rate;
    }

    /**
     * @param Rate $rate
     * @return Rate
     */
    public function update(Rate $rate): Rate
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @param Rate $rate
     * @return bool
     */
    public function delete(Rate $rate): bool
    {
        $this->getEntityManager()->remove($rate);
        $this->getEntityManager()->flush();

        return true;
    }
}
