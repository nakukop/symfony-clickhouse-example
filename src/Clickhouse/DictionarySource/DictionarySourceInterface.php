<?php

declare(strict_types=1);

namespace App\Clickhouse\DictionarySource;

interface DictionarySourceInterface
{
    public function getSourceConnectionString(): string;

    public function getLayout(): string;

    public function getLifetime(): int;
}
