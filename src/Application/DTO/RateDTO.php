<?php

namespace App\Application\DTO;

use App\Domain\Rate\Rate;

class RateDTO
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var int
     */
    public int $duration;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var int
     */
    public int $price;

    public static function create(Rate $rate): RateDTO
    {
        $dto = new self();
        $dto->name = $rate->getName();
        $dto->id = $rate->getId();
        $dto->description = $rate->getDescription();
        $dto->duration = $rate->getDuration();
        $dto->price = $rate->getPrice();

        return $dto;
    }

    /**
     * @param Rate[] $rates
     * @return array
     */
    public static function createFromCollection(array $rates): array
    {
        $collection = [];

        foreach ($rates as $rate) {
            $collection[] = self::create($rate);
        }

        return $collection;
    }
}