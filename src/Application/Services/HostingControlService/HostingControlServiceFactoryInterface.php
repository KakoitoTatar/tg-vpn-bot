<?php

declare (strict_types=1);

namespace App\Application\Services\HostingControlService;

interface HostingControlServiceFactoryInterface
{
    /**
     * @param string $name
     * @param array $credentials
     * @return HostingControlServiceInterface
     */
    public function build(string $name, array $credentials): HostingControlServiceInterface;
}
