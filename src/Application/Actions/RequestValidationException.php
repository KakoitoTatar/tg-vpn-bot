<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Exceptions\JsonEncodedException;

class RequestValidationException extends JsonEncodedException
{
}
