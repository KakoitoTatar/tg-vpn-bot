<?php

namespace App\Application\Services\DonationAlertsService;

interface DonationAlertsServiceInterface
{
    /**
     * @param int $page
     * @return array
     */
    public function getDonations(int $page): array;

    /**
     * @return array
     */
    public function getUser(): array;

    /**
     * @param int $clientId
     * @param string $token
     * @return array
     */
    public function subscribeToChannel(int $clientId, string $token): array;
}