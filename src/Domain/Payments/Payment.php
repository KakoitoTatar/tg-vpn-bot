<?php

declare(strict_types=1);

namespace App\Domain\Payments;

use App\Domain\Client\Client;
use App\Domain\Connection\Connection;
use App\Domain\Promocode\Promocode;
use App\Domain\Rate\Rate;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'payments')]
#[ORM\Entity(repositoryClass: PaymentRepositoryInterface::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Connection::class)]
    #[ORM\JoinColumn(name: 'connection_id', referencedColumnName: 'id', nullable: true)]
    private ?Connection $connection;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    private Client $client;

    #[ORM\Column(type: 'string', enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    #[ORM\Column(type: 'string')]
    private string $paymentSystemId;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $actualSum;

    #[ORM\Column(type: 'float')]
    private float $expectedSum;

    #[ORM\ManyToOne(targetEntity: Promocode::class)]
    private ?Promocode $promocode;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $reason;

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param Connection|null $connection
     */
    public function setConnection(?Connection $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * @return Connection|null
     */
    public function getConnection(): ?Connection
    {
        return $this->connection;
    }

    /**
     * @param float|null $actualSum
     */
    public function setActualSum(?float $actualSum): void
    {
        $this->actualSum = $actualSum;
    }

    /**
     * @return float|null
     */
    public function getActualSum(): ?float
    {
        return $this->actualSum;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param PaymentStatus $status
     */
    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return PaymentStatus
     */
    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    /**
     * @param string $paymentSystemId
     */
    public function setPaymentSystemId(string $paymentSystemId): void
    {
        $this->paymentSystemId = $paymentSystemId;
    }

    /**
     * @return string
     */
    public function getPaymentSystemId(): string
    {
        return $this->paymentSystemId;
    }

    /**
     * @param float $expectedSum
     */
    public function setExpectedSum(float $expectedSum): void
    {
        $this->expectedSum = $expectedSum;
    }

    /**
     * @return float
     */
    public function getExpectedSum(): float
    {
        return $this->expectedSum;
    }

    /**
     * @param Promocode $promocode
     */
    public function setPromocode(Promocode $promocode): void
    {
        $this->promocode = $promocode;
    }

    /**
     * @return Promocode|null
     */
    public function getPromocode(): ?Promocode
    {
        return $this->promocode;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}
