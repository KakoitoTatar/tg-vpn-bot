<?php

declare (strict_types=1);

namespace App\Application\Services\HostingControlService\Models;

readonly class BaseHostingModel
{
    /**
     * @param string $accountName
     * @param string $provider
     */
    public function __construct(
        protected string $accountName,
        protected string $provider
    ) {}

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
