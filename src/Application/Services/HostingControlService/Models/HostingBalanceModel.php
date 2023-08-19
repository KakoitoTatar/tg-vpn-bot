<?php

declare (strict_types=1);

namespace App\Application\Services\HostingControlService\Models;

readonly class HostingBalanceModel extends BaseHostingModel
{
    /**
     * @var float
     */
    protected float $summa;

    /**
     * @param string $accountName
     * @param string $provider
     * @param float $balance
     * @param float $bonus
     */
    public function __construct(
        string $accountName,
        string $provider,
        private float $balance,
        private float $bonus
    ) {
        $this->summa = $this->balance+$this->bonus;
        parent::__construct($accountName, $provider);
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @return float
     */
    public function getBonus(): float
    {
        return $this->bonus;
    }

    /**
     * @return float
     */
    public function getSumma(): float
    {
        return $this->summa;
    }
}
