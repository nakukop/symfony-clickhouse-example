<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine\Exception;

use Exception;

class EngineBaseException extends Exception
{
    public const INVALID_ENGINE_TYPE = 'Invalid engine type';

    public const UNAVAILABLE_USING_CONNECTION_SETTINGS = 'Can not use connection settings for this type';
}
