<?php

declare (strict_types=1);

namespace App\Domain\Instance;

interface InstanceRepositoryInterface
{
    /**
     * @param int $id
     * @return Instance
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return Instance
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Instance
     */
    public function findOneBy(array $conditions);

    /**
     * @param Instance $instance
     * @return Instance
     */
    public function save(Instance $instance): Instance;

    /**
     * @param Instance $instance
     * @return Instance
     */
    public function update(Instance $instance): Instance;

    /**
     * @param Instance $instance
     * @return bool
     */
    public function delete(Instance $instance): bool;
}
