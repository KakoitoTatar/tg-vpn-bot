<?php

declare (strict_types=1);


namespace App\Application\Services\HostingControlService;


use App\Application\Services\HostingControlService\Models\HostingBalanceModel;
use App\Application\Services\HostingControlService\Models\HostingLimitsModel;
use App\Application\Services\HostingControlService\Models\HostingServerModel;

interface HostingControlServiceInterface
{
        /**
     * @param string $login
     * @param string $password
     * @return string
     */
    public function authenticate(string $login, string $password): string;

    /**
     * @param string $token
     * @return HostingBalanceModel
     */
    public function getBalance(string $token): HostingBalanceModel;

    /**
     * @param string $token
     * @return HostingLimitsModel
     */
    public function getLimits(string $token): HostingLimitsModel;

    /**
     * @param string $token
     * @return HostingServerModel[]
     */
    public function list(string $token): array;

    public function getServer(string $token, string $serverId): HostingServerModel;

    /**
     * @param string $token
     * @param string $serverId
     * @return HostingServerModel
     */
    public function delete(string $token, string $serverId): HostingServerModel;

    /**
     * @param string $token
     * @return array
     */
    public function getDataCenters(string $token): array;

    /**
     * @param string $token
     * @return array
     */
    public function getPlans(string $token): array;

    /**
     * @param string $token
     * @return array
     */
    public function getTemplates(string $token): array;

    /**
     * @param string $token
     * @param string $dataCenter
     * @param string $plan
     * @param string $template
     * @param string $sshKey
     * @param string $name
     * @return HostingServerModel
     */
    public function create(
        string $token,
        string $dataCenter,
        string $plan,
        string $template,
        string $sshKey,
        string $name
    ): HostingServerModel;
}
