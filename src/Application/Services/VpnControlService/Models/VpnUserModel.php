<?php

declare (strict_types=1);

namespace App\Application\Services\VpnControlService\Models;

readonly class VpnUserModel
{
    /**
     * @param string $provider
     * @param string $id
     * @param string $name
     * @param array $connection
     * @param int|null $bandwidth
     */
    public function __construct(
        private string $provider,
        private string $id,
        private string $name,
        private array  $connection,
        private ?int $bandwidth = null,
    ) {}

    /**
     * @return int|null
     */
    public function getBandwidth(): ?int
    {
        return $this->bandwidth;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId(): string
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
}
