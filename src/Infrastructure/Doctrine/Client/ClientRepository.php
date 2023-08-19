<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Client;

use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\DomainException\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;

/**
 * @method find($id, $lockMode = null, $lockVersion = null)
 */
class ClientRepository extends EntityRepository implements ClientRepositoryInterface
{
    public function save(Client $client): Client
    {
        $this->getEntityManager()->persist($client);
        try {
            $this->getEntityManager()->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new UniqueConstraintViolationException(
                'Пользователь с данным email уже зарегистрирован',
                400
            );
        }

        return $client;
    }

    /**
     * @param Client $client
     * @return Client
     */
    public function update(Client $client): Client
    {
        $this->getEntityManager()->flush();

        return $client;
    }

    /**
     * @param $alias
     * @param $indexBy
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function createQueryBuilder($alias, $indexBy = null)
    {
        return parent::createQueryBuilder($alias, $indexBy); // TODO: Change the autogenerated stub
    }
}