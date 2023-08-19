<?php

namespace App\Application\DTO;

use App\Domain\Connection\Connection;
use DateTime;

class ConnectionDTO
{
    public int $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $activeTo;

    /**
     * @var string
     */
    public string $key;

    /**
     * @param int $id
     * @param string $name
     * @param DateTime $activeTo
     * @param string $key
     * @return ConnectionDTO
     */
    public static function create(int $id, string $name, DateTime $activeTo, string $key): ConnectionDTO
    {
        $dto = new self();
        $dto->id = $id;
        $dto->name = $name;
        $dto->activeTo = $activeTo->format('d.m.Y');
        $dto->key = $key;

        return $dto;
    }

    /**
     * @param array $connections
     * @return array
     */
    public static function createFromCollection(array $connections): array
    {
        $collection = [];

        foreach ($connections as $connection) {
            $collection[] = self::create(
                $connection['id'],
                $connection['name'],
                $connection['activeTo'],
                $connection['key']
            );
        }

        return $collection;
    }
}