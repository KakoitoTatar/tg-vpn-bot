<?php

namespace App\Infrastructure\Services\DonationAlertsService;

use App\Application\Services\DonationAlertsService\DonationAlertsServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class DonationAlertsService implements DonationAlertsServiceInterface
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param string $baseUri
     * @param string $authToken
     */
    public function __construct(string $baseUri, string $authToken)
    {
        $this->client = new Client([
            'base_uri' => $baseUri,
            'headers' => [
                'Authorization' => 'Bearer ' . $authToken
            ]
        ]);
    }

    /**
     * @param int $page
     * @return array
     * @throws GuzzleException
     */
    public function getDonations(int $page): array
    {
        $response = $this->client->get('/api/v1/alerts/donations?' . http_build_query(['page' => $page]));

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return array
     * @throws GuzzleException
     */
    public function getUser(): array
    {
        $response = $this->client->get('/api/v1/user/oauth');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param int $clientId
     * @param string $token
     * @return array
     * @throws GuzzleException
     */
    public function subscribeToChannel(int $clientId, string $token): array
    {
        $response = $this->client->post('/api/v1/centrifuge/subscribe', [
            'json' => [
                'channels' => ['$alerts:donation_' . $clientId],
                'client' => $token
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}