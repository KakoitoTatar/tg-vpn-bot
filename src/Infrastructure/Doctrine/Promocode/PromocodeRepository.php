<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Promocode;

use App\Domain\Promocode\Promocode;
use App\Domain\Promocode\PromocodeRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class PromocodeRepository extends EntityRepository implements PromocodeRepositoryInterface
{
    /**
     * @param Promocode $promocode
     * @return Promocode
     */
    public function save(Promocode $promocode): Promocode
    {
        $this->getEntityManager()->persist($promocode);
        $this->getEntityManager()->flush();

        return $promocode;
    }

    /**
     * @param Promocode $promocode
     * @return Promocode
     * @return Promocode
     */
    public function update(Promocode $promocode): Promocode
    {
        $this->getEntityManager()->flush();

        return $promocode;
    }
}