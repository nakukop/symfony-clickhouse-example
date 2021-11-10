<?php

declare(strict_types=1);

namespace App\Clickhouse\DictionarySource\Exception;

use Exception;

class DictionarySourceException extends Exception
{
    public const DICTIONARY_SOURCE_EMPTY = 'Dictionary source is not set';
}
