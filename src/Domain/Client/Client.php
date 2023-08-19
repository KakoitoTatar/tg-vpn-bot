<?php

declare (strict_types=1);

namespace App\Domain\Client;

use App\Domain\Connection\Connection;
use App\Domain\Promocode\Promocode;
use App\Domain\Rate\Rate;
use App\Domain\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'clients')]
#[ORM\Entity(repositoryClass: ClientRepositoryInterface::class)]
class Client
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id;

    private string $username;

    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $freeDays;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Connection::class)]
    private Collection $connections;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: Promocode::class)]
    private Promocode $affiliatedPromocode;

    public function __construct()
    {
        $this->connections = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return User::USER;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getFreeDays(): int
    {
        return $this->freeDays;
    }

    /**
     * @param int $freeDays
     * @return void
     */
    public function setFreeDays(int $freeDays): void
    {
        $this->freeDays = $freeDays;
    }

    /**
     * @return Connection[]
     */
    public function getConnections(): Collection
    {
        return $this->connections;
    }

    /**
     * @return Promocode
     */
    public function getAffiliatedPromocode(): Promocode
    {
        return $this->affiliatedPromocode;
    }

    /**
     * @param Promocode $affiliatedPromocode
     */
    public function setAffiliatedPromocode(Promocode $affiliatedPromocode): void
    {
        $this->affiliatedPromocode = $affiliatedPromocode;
    }
}
