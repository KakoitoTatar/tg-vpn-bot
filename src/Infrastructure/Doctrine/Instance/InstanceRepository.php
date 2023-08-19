<?php

declare (strict_types=1);

namespace App\Infrastructure\Doctrine\Instance;

use App\Domain\Instance\Instance;
use App\Domain\Instance\InstanceRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class InstanceRepository extends EntityRepository implements InstanceRepositoryInterface
{
    /**
     * @param Instance $instance
     * @return Instance
     */
    public function save(Instance $instance): Instance
    {
        $this->getEntityManager()->persist($instance);
        $this->getEntityManager()->flush();

        return $instance;
    }

    /**
     * @param Instance $instance
     * @return Instance
     */
    public function update(Instance $instance): Instance
    {
        $this->getEntityManager()->flush();

        return $instance;
    }

    /**
     * @param Instance $instance
     * @return bool
     */
    public function delete(Instance $instance): bool
    {
        $this->getEntityManager()->remove($instance);
        $this->getEntityManager()->flush();

        return true;
    }
}
