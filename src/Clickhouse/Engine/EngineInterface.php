<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine;

use App\Clickhouse\Engine\Exception\EngineBaseException;

interface EngineInterface
{
    /**
     * @throw EngineBaseException
     */
    public function getConnectionDsnString(): string;

    public function getConnectionSettingsString(): string;

    /**
     * @param array<string, string|int|bool> $settings
     *
     * @throws EngineBaseException
     */
    public function setConnectionSettings(array $settings): void;

    public function canUseConnectionSettings(): bool;
}
