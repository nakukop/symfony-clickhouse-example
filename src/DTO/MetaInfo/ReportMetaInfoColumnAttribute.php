<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoColumnAttribute",
 *     description="Object with attribute for columns metadata",
 *     required={"title", "sortable", "showByDefault"}
 * )
 */
class ReportMetaInfoColumnAttribute
{
    private string $title = '';

    /**
     * @OA\Property(type="boolean", property="sortable")
     */
    private bool $isSortable = false;

    /**
     * @OA\Property(type="boolean", property="showByDefault")
     */
    private bool $isShowByDefault = false;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    public function setIsSortable(bool $isSortable): void
    {
        $this->isSortable = $isSortable;
    }

    public function isShowByDefault(): bool
    {
        return $this->isShowByDefault;
    }

    public function setIsShowByDefault(bool $isShowByDefault): void
    {
        $this->isShowByDefault = $isShowByDefault;
    }
}
