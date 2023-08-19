<?php
declare(strict_types=1);

namespace App\Domain\Media;

interface MediaRepositoryInterface
{
    /**
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * @param array $conditions
     * @return mixed
     */
    public function findBy(array $conditions);

    /**
     * @param array $conditions
     * @return Media
     */
    public function findOneBy(array $conditions);

    /**
     * @param string $url
     * @return Media
     */
    public function save(string $url): Media;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
