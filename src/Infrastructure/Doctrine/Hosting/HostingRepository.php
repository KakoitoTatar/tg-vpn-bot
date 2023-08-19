<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Hosting;

use App\Domain\Hosting\Hosting;
use App\Domain\Hosting\HostingRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class HostingRepository extends EntityRepository implements HostingRepositoryInterface
{

    public function save(Hosting $hosting): Hosting
    {
        $this->getEntityManager()->persist($hosting);
        $this->getEntityManager()->flush();

        return $hosting;
    }

    public function update(Hosting $hosting): Hosting
    {
        $this->getEntityManager()->flush();

        return $hosting;
    }

    public function delete(Hosting $hosting): bool
    {
        $this->getEntityManager()->remove($hosting);
        $this->getEntityManager()->flush();

        return true;
    }
}
