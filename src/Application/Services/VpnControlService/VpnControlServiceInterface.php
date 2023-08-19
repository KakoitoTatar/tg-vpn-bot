<?php

declare (strict_types=1);

namespace App\Application\Services\VpnControlService;

use App\Application\Services\VpnControlService\Models\VpnUserModel;

interface VpnControlServiceInterface
{
    public function authenticate(array $params): string;

    public function getUsers(array $params): array;

    /**
     * @param array $params
     * @return VpnUserModel|null
     */
    public function createUser(array $params): ?VpnUserModel;

    /**
     * @param array $params
     * @return VpnUserModel
     */
    public function getUser(array $params): ?VpnUserModel;

    /**
     * @param array $params
     * @return bool
     */
    public function deleteUser(array $params): bool;

    /**
     * @param array $params
     * @return VpnUserModel|null
     */
    public function editUser(array $params): ?VpnUserModel;

    public function disableUser(array $params): bool;

    public function enableUser(array $params): bool;
}
