<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine;

final class EngineKafka extends AbstractEngine implements EngineInterface
{
    public const ENGINE_TYPE = 'Kafka';

    public function canUseConnectionSettings(): bool
    {
        return true;
    }

    public function getConnectionDsnString(): string
    {
        return sprintf('%s()', $this->getEngineType());
    }

    /**
     * @inheritDoc
     */
    public function setConnectionSettings(array $settings): void
    {
        $this->connectionSettings = $settings;
    }
}
