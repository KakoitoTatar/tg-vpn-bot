<?php

declare (strict_types=1);

namespace App\Infrastructure\Services\VpnControlService;

use App\Application\Services\VpnControlService\Models\VpnUserModel;
use App\Application\Services\VpnControlService\VpnControlServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OutlineControlService implements VpnControlServiceInterface
{
    /**
     * @var string
     */
    private string $provider = 'outline';

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param array $params
     * @return string
     */
    public function authenticate(array $params): string
    {
        $this->client = new Client([
            'base_uri' => $params['base_uri'],
            'verify' => false
        ]);

        return 'success';
    }

    /**
     * @param array $params
     * @return array
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getUsers(array $params): array
    {
        $rawResponse = $this->client->get('access-keys');

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $users = [];

        foreach ($response['accessKeys'] as $key) {
            $users[] = new VpnUserModel(
                $this->provider,
                $key['id'],
                $key['name'],
                ['accessUrl' => $key['accessUrl']]
            );
        }

        return $users;
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     * @throws \JsonException
     */
    public function createUser(array $params): ?VpnUserModel
    {
        try {
            $rawCreateResponse = $this->client->post(
                'access-keys',
                ['json' => ['method' => 'aes-192-gcm']]
            );

            $createResponse = json_decode(
                $rawCreateResponse->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $rawRenameResponse = $this->client->put(
                'access-keys/' . $createResponse['id'] . '/name',
                ['json' => ['name' => 'tg_connection_' . $params['name']]]
            );

            if ($rawRenameResponse->getStatusCode() === 204) {
                return new VpnUserModel(
                    $this->provider,
                    $createResponse['id'],
                    'tg_connection_' . $params['name'],
                    ['accessUrl' => $createResponse['accessUrl']]
                );
            }

            return null;

        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getUser(array $params): ?VpnUserModel
    {
        $rawResponse = $this->client->get('access-keys');
        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $bandwidthRaw = $this->client->get('metrics/transfer');
        $bandwidth = json_decode(
            $bandwidthRaw->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        
        foreach ($response['accessKeys'] as $key) {
            if ($key['id'] === $params['id']) {
                $dataTransfered = $bandwidth['bytesTransferredByUserId'][$key['id']] ?? 0;
                return new VpnUserModel(
                    $this->provider,
                    $key['id'],
                    $key['name'],
                    ['accessUrl' => $key['accessUrl']],
                    $dataTransfered
                );
            }
        }

        return null;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function deleteUser(array $params): bool
    {
        try {
            $rawResponse = $this->client->delete('access-keys/' . $params['id']);

            return $rawResponse->getStatusCode() === 204;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     * @throws \JsonException
     */
    public function editUser(array $params): ?VpnUserModel
    {
        $user = $this->getUser(['id' => $params['id']]);

        if ($user === null) {
            return null;
        }

        try {
            $rawResponse = $this->client->put(
                'access-keys/' . $params['id'] . '/name',
                ['json' => ['name' => $params['name']]]
            );

            if ($rawResponse->getStatusCode() === 204) {
                return new VpnUserModel(
                    $this->provider,
                    $user->getId(),
                    $params['name'],
                    $user->getConnection()
                );
            }

            return null;

        } catch (GuzzleException) {
            return null;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function disableUser(array $params): bool
    {
        try {
            $rawResponse = $this->client->put(
                'access-keys/' . $params['id'] . '/data-limit',
                [
                    'json' => ['limit' => ['bytes' => 0]]
                ]
            );

            return $rawResponse->getStatusCode() === 204;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function enableUser(array $params): bool
    {
        try {
            $rawResponse = $this->client->delete('access-keys/' . $params['id'] . '/data-limit');

            return $rawResponse->getStatusCode() === 204;
        } catch (GuzzleException) {
            return false;
        }
    }
}
