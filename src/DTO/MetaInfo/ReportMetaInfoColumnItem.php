<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoColumnItem",
 *     description="Object of item for list in ReportMetaInfoColumns",
 *     required={"id", "type", "attributes"}
 * )
 */
class ReportMetaInfoColumnItem
{
    private const TYPE_DEFAULT = 'column';

    private string $id = '';

    private string $type = self::TYPE_DEFAULT;
    
    private ReportMetaInfoColumnAttribute $attributes;

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

    public function getAttributes(): ReportMetaInfoColumnAttribute
    {
        return $this->attributes;
    }

    public function setAttributes(ReportMetaInfoColumnAttribute $attributes): void
    {
        $this->attributes = $attributes;
    }
}
