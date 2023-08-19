<?php

declare (strict_types=1);

namespace App\Infrastructure\Services\HostingControlServices;

use App\Application\Services\HostingControlService\HostingControlServiceInterface;

abstract class AbstractHostingControlService implements HostingControlServiceInterface
{
    /**
     * @var string
     */
    protected string $accountName;

    /**
     * @var string
     */
    protected string $provider;
}
