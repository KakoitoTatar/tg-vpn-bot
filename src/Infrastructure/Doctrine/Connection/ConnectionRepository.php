<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Connection;

use App\Domain\Connection\Connection;
use App\Domain\Connection\ConnectionRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class ConnectionRepository extends EntityRepository implements ConnectionRepositoryInterface
{

    /**
     * @param Connection $connection
     * @return Connection
     */
    public function save(Connection $connection): Connection
    {
        $this->getEntityManager()->persist($connection);
        $this->getEntityManager()->flush();

        return $connection;
    }

    /**
     * @param Connection $connection
     * @return Connection
     */
    public function update(Connection $connection): Connection
    {
        $this->getEntityManager()->flush();

        return $connection;
    }

    /**
     * @param Connection $connection
     * @return mixed|void
     */
    public function delete(Connection $connection)
    {
        $this->getEntityManager()->remove($connection);
        $this->getEntityManager()->flush();
    }
}