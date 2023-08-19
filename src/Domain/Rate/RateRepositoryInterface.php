<?php

declare (strict_types=1);

namespace App\Domain\Rate;

interface RateRepositoryInterface
{
    /**
     * @param int $id
     * @return Rate
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return mixed
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Rate
     */
    public function findOneBy(array $conditions);

    /**
     * @param Rate $hosting
     * @return Rate
     */
    public function save(Rate $rate): Rate;

    /**
     * @param Rate $hosting
     * @return Rate
     */
    public function update(Rate $rate): Rate;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(Rate $rate): bool;
}
