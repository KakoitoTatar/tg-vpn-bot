<?php

declare(strict_types=1);

namespace App\Domain\Promocode;

use Doctrine\Common\Collections\Criteria;

interface PromocodeRepositoryInterface
{
    /**
     * @param int $id
     * @return Promocode
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Promocode
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Promocode
     */
    public function findOneBy(array $conditions);

    /**
     * @param Promocode $promocode
     * @return Promocode
     */
    public function save(Promocode $promocode): Promocode;

    /**
     * @param Promocode $promocode
     * @return Promocode
     */
    public function update(Promocode $promocode): Promocode;

    /**
     * @param Criteria $criteria
     * @return mixed
     */
    public function matching(Criteria $criteria);
}