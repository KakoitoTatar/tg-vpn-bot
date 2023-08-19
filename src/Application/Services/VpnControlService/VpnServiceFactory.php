<?php

declare (strict_types=1);

namespace App\Application\Services\VpnControlService;

use App\Domain\Instance\Instance;
use App\Infrastructure\Services\VpnControlService\OutlineControlService;
use App\Infrastructure\Services\VpnControlService\WireguardControlService;
use function Symfony\Component\String\b;

class VpnServiceFactory
{
    /**
     * @param string $provider
     * @return VpnControlServiceInterface
     */
    public static function getControlService(string $provider): VpnControlServiceInterface
    {
        switch ($provider) {
            case Instance::OUTLINE:
            default:
                return new OutlineControlService();
            case Instance::WIREGUARD:
                return new WireguardControlService();
        }
    }
}
