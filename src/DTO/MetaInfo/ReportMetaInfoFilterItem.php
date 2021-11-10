<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoFilterItem",
 *     description="Object of item for list in ReportMetaInfo",
 *     required={"id", "type", "attributes"}
 * )
 */
class ReportMetaInfoFilterItem
{
    private string $id = '';

    private string $type = '';

    private ReportMetaInfoFilterItemAttribute $attributes;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAttributes(): ReportMetaInfoFilterItemAttribute
    {
        return $this->attributes;
    }

    public function setAttributes(ReportMetaInfoFilterItemAttribute $attributes): void
    {
        $this->attributes = $attributes;
    }
}
