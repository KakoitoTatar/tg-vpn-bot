<?php

declare (strict_types=1);

namespace App\Infrastructure\Services\HostingControlServices;

use App\Application\Services\HostingControlService\HostingControlServiceInterface;
use App\Application\Services\HostingControlService\Models\HostingBalanceModel;
use App\Application\Services\HostingControlService\Models\HostingLimitsModel;
use App\Application\Services\HostingControlService\Models\HostingServerModel;
use Doctrine\ORM\Cache\Exception\FeatureNotImplemented;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use function _PHPStan_b8e553790\RingCentral\Psr7\str;

class VdsinaControlService extends AbstractHostingControlService
{
    private const SERVER_STATUSES = [
        'new' => HostingServerModel::STATUS_NEW,
        'active' => HostingServerModel::STATUS_ACTIVE,
        'block' => HostingServerModel::STATUS_BLOCKED,
        'notpaid' => HostingServerModel::STATUS_NOTPAID,
        'deleted' => HostingServerModel::STATUS_DELETED,
    ];

    protected string $provider = 'VDSina';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://userapi.vdsina.ru']);
    }

    /**
     * @param string $login
     * @param string $password
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function authenticate(string $login, string $password): string
    {
        $this->accountName = $login;
        $rawResponse = $this->client->post('/v1/auth', [
            'json' => ['email' => $login, 'password' => $password]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $response['data']['token'];
    }

    /**
     * @param string $token
     * @return HostingBalanceModel
     * @throws \JsonException
     */
    public function getBalance(string $token): HostingBalanceModel
    {
        $rawResponse = $this->client->get('/v1/account.balance', [
            'headers' => ['Authorization' => $token]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['data'];

        return new HostingBalanceModel(
            $this->accountName,
            $this->provider,
            (float) $response['real'],
            (float) $response['bonus']+ (float) $response['partner']
        );
    }

    /**
     * @param string $token
     * @return HostingLimitsModel
     */
    public function getLimits(string $token): HostingLimitsModel
    {
        $rawResponse = $this->client->get('/v1/account.limit', [
            'headers' => ['Authorization' => $token]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['data'];

        return new HostingLimitsModel(
            $this->accountName,
            $this->provider,
            $response['server']['now'],
            $response['server']['max']
        );
    }

    /**
     * @param string $token
     * @return HostingServerModel[]
     */
    public function list(string $token): array
    {
        $rawResponse = $this->client->get('/v1/server', [
            'headers' => ['Authorization' => $token]
        ]);

        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $servers = [];

        foreach ($response['data'] as $serverInfo) {
            $servers[] = $this->getServer($token, (string) $serverInfo['id']);
        }

        return $servers;
    }

    /**
     * @param string $token
     * @param string $serverId
     * @return HostingServerModel
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function getServer(string $token, string $serverId): HostingServerModel
    {
        $rawResponse = $this->client->get('/v1/server/' . $serverId, [
            'headers' => ['Authorization' => $token]
        ]);
        $response = json_decode(
            $rawResponse->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        )['data'];

        return new HostingServerModel(
            $this->accountName,
            $this->provider,
            (string) $response['id'],
            $response['name'],
            self::SERVER_STATUSES[$response['status']],
            $response['ip'][0]['ip'],
            $response['datacenter']['country'],
            $response['datacenter']['name'],
            $response['bandwidth']['current_month'],
            $response['data']['traff']['bytes']
        );
    }

    /**
     * @param string $token
     * @param string $serverId
     * @return HostingServerModel
     * @throws FeatureNotImplemented
     */
    public function delete(string $token, string $serverId): HostingServerModel
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @param string $token
     * @return array
     * @throws FeatureNotImplemented
     */
    public function getDataCenters(string $token): array
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @param string $token
     * @return array
     * @throws FeatureNotImplemented
     */
    public function getPlans(string $token): array
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @param string $token
     * @return array
     * @throws FeatureNotImplemented
     */
    public function getTemplates(string $token): array
    {
        throw new FeatureNotImplemented();
    }

    /**
     * @param string $token
     * @param string $dataCenter
     * @param string $plan
     * @param string $template
     * @param string $sshKey
     * @param string $name
     * @return HostingServerModel
     * @throws FeatureNotImplemented
     */
    public function create(
        string $token,
        string $dataCenter,
        string $plan,
        string $template,
        string $sshKey,
        string $name
    ): HostingServerModel {
        throw new FeatureNotImplemented();
    }
}
