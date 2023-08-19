<?php

declare (strict_types=1);

namespace App\Application\Services\HostingControlService\Models;

readonly class HostingServerModel extends BaseHostingModel
{
    public const STATUS_NEW = 'new';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DELETED = 'deleted';

    public const STATUS_BLOCKED = 'blocked';

    public const STATUS_NOTPAID = 'notpaid';

    public function __construct(
        string $accountName,
        string $provider,
        private string $id,
        private string $name,
        private string $status,
        private string $ip,
        private string $country,
        private string $countryHRF,
        private ?int $bandwidth = null,
        private ?int $maxBandwidth = null
    ) {
        parent::__construct($accountName, $provider);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCountryHRF(): string
    {
        return $this->countryHRF;
    }

    /**
     * @return int|null
     */
    public function getBandwidth(): ?int
    {
        return $this->bandwidth;
    }

    /**
     * @return int|null
     */
    public function getMaxBandwidth(): ?int
    {
        return $this->maxBandwidth;
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
    public function getAccountName(): string
    {
        return $this->accountName;
    }
}
