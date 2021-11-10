<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoFilter",
 *     description="Report's meta-information object for request filter.",
 *     required={"data"}
 * )
 */
class ReportMetaInfoFilter
{
    /**
     * @var array<ReportMetaInfoFilterItem>
     */
    private array $data;

    public function __construct(?array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @return array<ReportMetaInfoFilterItem>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<ReportMetaInfoFilterItem> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
