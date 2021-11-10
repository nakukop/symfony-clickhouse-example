<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\DTO\Pagination\Pagination;
use App\Validator\ReportRequestDtoRawConstraint;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="RequestFilter",
 *     description="Request Filter Object",
 * )
 */
#[ReportRequestDtoRawConstraint]
final class ReportRequestDto
{
    public const REPORT_REQUEST_KEY_FILTERS = 'filters';

    public const REPORT_REQUEST_KEY_SORT = 'sort';

    public const REPORT_REQUEST_KEY_SHOW_COLUMNS = 'showColumns';

    public const REPORT_REQUEST_KEY_PAGINATION = 'pagination';

    public const PAGINATION_PER_PAGE_KEY = 'perPage';

    public const PAGINATION_PAGE_KEY = 'page';

    public const PAGINATION_PER_PAGE_VALUE_MAX = 1_000;

    public const PAGINATION_PER_PAGE_VALUE_DEFAULT = 100;

    /**
     * @var array<RequestFilterField> $filters
     */
    private array $filters = [];

    /**
     * @var array<string>
     */
    private array $sort = [];

    /**
     * @var array<string>
     */
    private array $showColumns = [];

    /**
     * @OA\Property(type="string", readOnly=true)
     * @var array<string, array<string|array>>
     */
    private array $dataRawRequest = [];

    private Pagination $pagination;

    /**
     * @param array<string, array<string|array>> $dataRawRequest
     */
    public function __construct(array $dataRawRequest)
    {
        $this->dataRawRequest = $dataRawRequest;
        $this->pagination = new Pagination();
    }

    /**
     * @return array<RequestFilterField>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param array<RequestFilterField> $filters
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @return array<string>
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param array<string> $sort
     */
    public function setSort(array $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return array<string>
     */
    public function getShowColumns(): array
    {
        return $this->showColumns;
    }

    /**
     * @param array<string> $showColumns
     */
    public function setShowColumns(array $showColumns): void
    {
        $this->showColumns = $showColumns;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function setPagination(Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    /**
     * @return array<string, array<string|array>>
     */
    public function getDataRawRequest(): array
    {
        return $this->dataRawRequest;
    }
}
