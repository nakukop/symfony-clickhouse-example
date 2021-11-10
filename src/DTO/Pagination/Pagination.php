<?php

declare(strict_types=1);

namespace App\DTO\Pagination;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Pagination",
 *     description="Pagination Object",
 *     required={"page", "perPage"}
 * )
 */
class Pagination
{
    /**
     * @OA\Property(type="integer")
     */
    private int $page = 1;

    /**
     * @OA\Property(type="integer")
     */
    private int $perPage = 20;

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int|string $page): void
    {
        $this->page = (int)$page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int|string $perPage): void
    {
        $this->perPage = (int)$perPage;
    }
}
