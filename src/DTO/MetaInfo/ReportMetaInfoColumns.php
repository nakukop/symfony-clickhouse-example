<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoColumns",
 *     description="Object with report columns metadata",
 *     required={"data"}
 * )
 */
class ReportMetaInfoColumns
{
    /**
     * @var array<ReportMetaInfoColumnItem>
     */
    private array $data;

    /**
     * @param array<ReportMetaInfoColumnItem>|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @return array<ReportMetaInfoColumnItem>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<ReportMetaInfoColumnItem> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
