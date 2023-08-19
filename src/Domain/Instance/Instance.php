<?php

declare (strict_types=1);

namespace App\Domain\Instance;

use App\Domain\Connection\Connection;
use App\Domain\Hosting\Hosting;
use App\Domain\Rate\Rate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'instances')]
#[ORM\Entity(repositoryClass: InstanceRepositoryInterface::class)]
class Instance
{
    public const OUTLINE = 'outline';

    public const WIREGUARD = 'wireguard';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $country;

    #[ORM\Column(type: 'integer')]
    private int $capacity;

    #[ORM\Column(type: 'string')]
    private string $protocol;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\ManyToOne(targetEntity: Hosting::class, inversedBy: 'instances')]
    #[ORM\JoinColumn(name: 'hosting_id', referencedColumnName: 'id')]
    private Hosting $hosting;

    #[ORM\Column(type: 'json')]
    private array $connection;

    #[ORM\ManyToMany(targetEntity: Rate::class, inversedBy: 'instances')]
    #[ORM\JoinTable(name: 'instances_rates')]
    private Collection $rates;

    #[ORM\OneToMany(mappedBy: 'instance', targetEntity: Connection::class)]
    private Collection $connnections;

    public function __construct()
    {
        $this->rates = new ArrayCollection();
        $this->connnections = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getConnection(): array
    {
        return $this->connection;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param int $capacity
     * @return $this
     */
    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @param array $connection
     * @return $this
     */
    public function setConnection(array $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @param string $protocol
     * @return $this
     */
    public function setProtocol(string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @return int
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * @return string
     */
    public function getProtocol(): string
    {
        return $this->protocol;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param Rate $rate
     * @return $this
     */
    public function addRate(Rate $rate): self
    {
        $this->rates->add($rate);

        return $this;
    }

    /**
     * @param Rate $rate
     * @return $this
     */
    public function removeRate(Rate $rate): self
    {
        $this->rates->removeElement($rate);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    /**
     * @param Hosting $hosting
     * @return $this
     */
    public function setHosting(Hosting $hosting): self
    {
        $this->hosting = $hosting;

        return $this;
    }

    /**
     * @return Hosting
     */
    public function getHosting(): Hosting
    {
        return $this->hosting;
    }

    /**
     * @return Collection
     */
    public function getConnnections(): Collection
    {
        return $this->connnections;
    }
}
