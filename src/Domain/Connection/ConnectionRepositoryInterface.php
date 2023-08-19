<?php

namespace App\Domain\Connection;

use App\Domain\Payments\Payment;
use Doctrine\Common\Collections\Criteria;

interface ConnectionRepositoryInterface
{
    /**
     * @param int $id
     * @return Connection
     */
    public function find(int $id);

    /**
     * @param Connection $connection
     * @return Connection
     */
    public function save(Connection $connection): Connection;

    /**
     * @param Connection $connection
     * @return Connection
     */
    public function update(Connection $connection): Connection;

    /**
     * @param array $conditions
     * @return Connection
     */
    public function findOneBy(array $conditions);

    /**
     * @param Criteria $criteria
     * @return mixed
     */
    public function matching(Criteria $criteria);

    /**
     * @param $alias
     * @param $indexBy
     * @return mixed
     */
    public function createQueryBuilder($alias, $indexBy = null);

    /**
     * @param array $conditions
     * @return Payment
     */
    public function findBy(array $conditions);

    /**
     * @param Connection $connection
     * @return mixed
     */
    public function delete(Connection $connection);
}