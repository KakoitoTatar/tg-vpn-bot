<?php

namespace App\Domain\Connection;

use App\Domain\Client\Client;
use App\Domain\Instance\Instance;
use App\Domain\Rate\Rate;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'connections')]
#[ORM\Entity(repositoryClass: ConnectionRepositoryInterface::class)]
class Connection
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Instance::class)]
    private Instance $instance;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    private Client $client;

    #[ORM\ManyToOne(targetEntity: Rate::class)]
    private Rate $rate;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $vpnKey;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $periodStart = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $periodEnd = null;

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
     * @return Rate
     */
    public function getRate(): Rate
    {
        return $this->rate;
    }

    /**
     * @param Rate $rate
     */
    public function setRate(Rate $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return Instance
     */
    public function getInstance(): Instance
    {
        return $this->instance;
    }

    /**
     * @param Instance $instance
     */
    public function setInstance(Instance $instance): void
    {
        $this->instance = $instance;
    }

    /**
     * @return DateTime|null
     */
    public function getPeriodEnd(): ?DateTime
    {
        return $this->periodEnd;
    }

    /**
     * @param DateTime $periodEnd
     */
    public function setPeriodEnd(DateTime $periodEnd): void
    {
        $this->periodEnd = $periodEnd;
    }

    /**
     * @return DateTime|null
     */
    public function getPeriodStart(): ?DateTime
    {
        return $this->periodStart;
    }

    /**
     * @param DateTime $periodStart
     */
    public function setPeriodStart(DateTime $periodStart): void
    {
        $this->periodStart = $periodStart;
    }

    /**
     * @return string|null
     */
    public function getVpnKey(): ?string
    {
        return $this->vpnKey;
    }

    /**
     * @param string|null $vpnKey
     * @return void
     */
    public function setVpnKey(?string $vpnKey): void
    {
        $this->vpnKey = $vpnKey;
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
    public function isActive(): bool
    {
        return $this->active;
    }
}