<?php

declare (strict_types=1);

namespace App\Domain\Client;

use App\Domain\Instance\Instance;
use Doctrine\Common\Collections\Criteria;

interface ClientRepositoryInterface
{
    /**
     * @param int $id
     * @return Client
     */
    public function find($id, $lockMode = null, $lockVersion = null);

    /**
     * @param Client $client
     * @return Client
     */
    public function save(Client $client): Client;

    /**
     * @param Client $client
     * @return Client
     */
    public function update(Client $client): Client;

    /**
     * @param array $conditions
     * @return Instance
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
}
