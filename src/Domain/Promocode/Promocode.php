<?php

declare(strict_types=1);

namespace App\Domain\Promocode;

use App\Domain\Client\Client;
use App\Domain\Rate\Rate;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'promocodes')]
#[ORM\Entity(repositoryClass: PromocodeRepositoryInterface::class)]
class Promocode
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $name;

    #[ORM\Column(type: 'string', enumType: PromocodeTypes::class)]
    private PromocodeTypes $type;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    private ?Client $client;

    #[ORM\ManyToOne(targetEntity: Rate::class)]
    private ?Rate $rate;

    #[ORM\OneToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id')]
    private ?Client $owner;

    #[ORM\Column(type: 'boolean')]
    private bool $multipleUse;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Rate|null
     */
    public function getRate(): ?Rate
    {
        return $this->rate;
    }

    /**
     * @param Rate|null $rate
     */
    public function setRate(?Rate $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     */
    public function setClient(?Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isMultipleUse(): bool
    {
        return $this->multipleUse;
    }

    /**
     * @param bool $multipleUse
     */
    public function setMultipleUse(bool $multipleUse): void
    {
        $this->multipleUse = $multipleUse;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param PromocodeTypes $type
     */
    public function setType(PromocodeTypes $type): void
    {
        $this->type = $type;
    }

    /**
     * @return PromocodeTypes
     */
    public function getType(): PromocodeTypes
    {
        return $this->type;
    }

    /**
     * @param Client|null $owner
     */
    public function setOwner(?Client $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return Client|null
     */
    public function getOwner(): ?Client
    {
        return $this->owner;
    }
}