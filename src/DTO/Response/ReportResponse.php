<?php

declare(strict_types=1);

namespace App\DTO\Response;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportResponse",
 *     description="Report Response Object",
 *     required={"data", "total"}
 * )
 */
class ReportResponse
{
    /**
     * @var array<ReportResponseItem>
     */
    private array $data = [];

    private int $total = 0;

    /**
     * @return array<ReportResponseItem>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<ReportResponseItem> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}
