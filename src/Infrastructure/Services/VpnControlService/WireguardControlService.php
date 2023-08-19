<?php

declare (strict_types=1);

namespace App\Infrastructure\Services\VpnControlService;

use App\Application\Services\VpnControlService\Models\VpnUserModel;
use App\Application\Services\VpnControlService\VpnControlServiceInterface;
use GuzzleHttp\Client;

class WireguardControlService implements VpnControlServiceInterface
{
    /**
     * @var string
     */
    private string $provider = 'wireguard';

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
            'headers' => [
                'Authorization' => 'Bearer ' . $params['token'],
                'Content-Type' => 'application/json'
            ]
        ]);

        return 'success';
    }

    /**
     * @param array $params
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function getUsers(array $params): array
    {
        $rawResponse = $this->client->get('/v1/devices/wg0/peers/');

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $users = [];

        foreach ($response as $key) {
            if (!isset($key['user'])) {
                continue;
            }

            $connectionConfig = $this->client
                ->get('/v1/devices/wg0/peers/' . $key['url_safe_public_key'] . '/quick.conf');

            $users[] = new VpnUserModel(
                $this->provider,
                $key['url_safe_public_key'],
                $key['user']['name'],
                ['config' => $connectionConfig->getBody()->getContents()]
            );
        }

        return $users;
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function createUser(array $params): ?VpnUserModel
    {
        $rawResponse = $this->client->post('/v1/users/', [
            'json' => ['name' => $params['name']]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $userId = $response['id'];

        $strong = true;

        $rawResponse =  $this->client->post('/v1/devices/wg0/peers/', [
            'json' => [
                'user_id' => $userId,
                'persistent_keepalive_interval' => '25s',
                'preshared_key' => base64_encode(openssl_random_pseudo_bytes(32, $strong))
            ]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $connectionConfig = $this->client
            ->get('/v1/devices/wg0/peers/' . $response['url_safe_public_key'] . '/quick.conf');

        return new VpnUserModel(
            $this->provider,
            $response['public_key'],
            $response['user']['name'],
            ['config' => $connectionConfig->getBody()->getContents()]
        );
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     */
    public function getUser(array $params): ?VpnUserModel
    {
        $rawResponse = $this->client->get('/v1/devices/wg0/peers/' . $params['id'] . '/');

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $connectionConfig = $this->client
            ->get('/v1/devices/wg0/peers/' . $response['url_safe_public_key'] . '/quick.conf');

        return new VpnUserModel(
            $this->provider,
            $response['url_safe_public_key'],
            $response['user']['name'],
            ['config' => $connectionConfig->getBody()->getContents()],
            $response['transmit_bytes']+$response['receive_bytes']
        );
    }

    /**
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function deleteUser(array $params): bool
    {
        $rawResponse = $this->client->get('/v1/devices/wg0/peers/' . $params['id'] . '/');

        $user = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $peerDeleteResponse = $this->client->delete('/devices/wg0/peers/' . $user['url_safe_public_key'] . '/');

        if($peerDeleteResponse->getStatusCode() !== 404) {
            $userDeleteResponse = $this->client->delete('/users/' . $user['user']['id'] . '/');
            if ($userDeleteResponse->getStatusCode() !== 404) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $params
     * @return VpnUserModel|null
     * @throws \JsonException
     */
    public function editUser(array $params): ?VpnUserModel
    {
        return $this->getUser($params);
    }

    /**
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function disableUser(array $params): bool
    {
        $rawResponse = $this->client->get('/v1/devices/wg0/peers/' . $params['id'] . '/disable/');

        return $rawResponse->getStatusCode() !== 404;
    }

    /**
     * @param array $params
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function enableUser(array $params): bool
    {
        $rawResponse = $this->client->get('/v1/devices/wg0/peers/' . $params['id'] . '/enable/');

        return $rawResponse->getStatusCode() !== 404;
    }
}
