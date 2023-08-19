<?php

declare (strict_types=1);

namespace App\Domain\Hosting;

interface HostingRepositoryInterface
{
    /**
     * @param int $id
     * @return Hosting
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return mixed
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Hosting
     */
    public function findOneBy(array $conditions);

    /**
     * @param Hosting $hosting
     * @return Hosting
     */
    public function save(Hosting $hosting): Hosting;

    /**
     * @param Hosting $hosting
     * @return Hosting
     */
    public function update(Hosting $hosting): Hosting;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(Hosting $hosting): bool;
}
