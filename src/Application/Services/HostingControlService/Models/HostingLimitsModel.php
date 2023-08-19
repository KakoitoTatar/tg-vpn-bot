<?php

declare (strict_types=1);

namespace App\Application\Services\HostingControlService\Models;

readonly class HostingLimitsModel extends BaseHostingModel
{
    /**
     * @param string $accountName
     * @param string $provider
     * @param int $amountOfServers
     * @param int $maxAmountOfServers
     */
    public function __construct(
        string $accountName,
        string $provider,
        private int $amountOfServers,
        private int $maxAmountOfServers
    ) {
        parent::__construct($accountName, $provider);
    }

    /**
     * @return int
     */
    public function getAmountOfServers(): int
    {
        return $this->amountOfServers;
    }

    /**
     * @return int
     */
    public function getMaxAmountOfServers(): int
    {
        return $this->maxAmountOfServers;
    }
}
