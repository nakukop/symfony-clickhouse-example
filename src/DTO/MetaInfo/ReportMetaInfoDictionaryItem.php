<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoDictionaryItem",
 *     description="Object of item for attribute's dictionary in ReportMetaInfoFilterItemAttribute",
 *     required={"label", "value"}
 * )
 */
class ReportMetaInfoDictionaryItem
{
    private string $label;

    private string|int|float|bool $value;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getValue(): bool|int|float|string
    {
        return $this->value;
    }

    public function setValue(bool|int|float|string $value): void
    {
        $this->value = $value;
    }
}
