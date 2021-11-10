<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine;

class EngineMergeTree extends AbstractEngine implements EngineInterface
{
    public const ENGINE_TYPE = 'MergeTree';

    public function getConnectionDsnString(): string
    {
        if ($this->isValidConfString()) {
            return $this->getConfString();
        }

        return $this->getEngineType() . '()';
    }

    /**
     * @inheritDoc
     */
    public function setConnectionSettings(array $settings): void
    {
        $this->connectionSettings = $settings;
    }

    public function canUseConnectionSettings(): bool
    {
        return true;
    }

    /**
     * @return array<int, string>
     */
    public function getSubtypes(): array
    {
        return [
            'ReplicatedMergeTree',
            'ReplicatedSummingMergeTree',
            'ReplicatedReplacingMergeTree',
            'ReplicatedAggregatingMergeTree',
            'ReplicatedCollapsingMergeTree',
            'ReplicatedVersionedCollapsingMergeTree',
            'ReplicatedGraphiteMergeTree',
            'ReplacingMergeTree',
            'SummingMergeTree',
            'AggregatingMergeTree',
            'CollapsingMergeTree',
            'VersionedCollapsingMergeTree',
            'GraphiteMergeTree',
        ];
    }
}
