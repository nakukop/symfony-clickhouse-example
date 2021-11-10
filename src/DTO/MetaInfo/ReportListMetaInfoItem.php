<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportListMetaInfoItem",
 *     description="Object of item for list in ReportListMetaInfo",
 *     required={"id", "category"}
 * )
 */
class ReportListMetaInfoItem
{
    private string $id = '';

    private string $category = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }
}
