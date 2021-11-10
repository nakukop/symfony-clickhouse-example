<?php

declare(strict_types=1);

namespace App\Service\Report;

use App\DB\Connection;
use App\DTO\Request\ReportRequest;
use App\DTO\Response\ReportResponse;
use App\Service\ReportQueryBuilder;

abstract class AbstractReport implements ReportInterface
{
    protected const ITEMS_COUNT_MAX = 1_000;

    protected const ITEMS_COUNT_DEFAULT = 100;

    protected ReportRequest $reportRequest;

    /**
     * @inheritDoc
     */
    abstract public function getRequiredColumns(): array;

    /**
     * @inheritDoc
     */
    abstract public function getDefaultColumns(): array;

    /**
     * @inheritDoc
     */
    abstract public function getAllColumns(): array;

    /**
     * @inheritDoc
     */
    abstract public function getSortColumns(): array;

    /**
     * @inheritDoc
     */
    abstract public function getGroupByColumns(): array;

    abstract public function prepareStatement(): ReportInterface;

    abstract public function getReportResponse(): ReportResponse;

    public function __construct(protected ReportQueryBuilder $queryBuilder, protected Connection $connection)
    {
    }

    public function setReportRequest(ReportRequest $reportRequest): ReportInterface
    {
        $this->reportRequest = $reportRequest;

        return $this;
    }

    protected function getQueryBuilder(): ReportQueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function getConnection(): Connection
    {
        return $this->connection;
    }

    protected function getReportRequest(): ReportRequest
    {
        return $this->reportRequest;
    }

    protected function isNeedShowColumn(string $columnName): bool
    {
        $columnsForShow = $this->getReportRequest()->getRequestDto()->getShowColumns();

        return in_array($columnName, $columnsForShow, true)
            || in_array($columnName, $this->getRequiredColumns(), true)
            || (
                $columnsForShow === []
                && in_array($columnName, $this->getDefaultColumns(), true)
            )
            ;
    }

    protected function isNeedSortColumn(string $columnName): bool
    {
        $columnsForSort = $this->getReportRequest()->getRequestDto()->getSort();

        $trimmedColumnName = ltrim($columnName, '-');

        return
            in_array($trimmedColumnName, $this->getSortColumns(), true)
            && (
                in_array($trimmedColumnName, $columnsForSort, true)
                || in_array('-' . $trimmedColumnName, $columnsForSort, true)
            )
            ;
    }

    /**
     * @return array<int, int>
     */
    protected function getPaginationParameters(): array
    {
        $pagination = $this->getReportRequest()->getRequestDto()->getPagination();

        $limit = $pagination->getPerPage() > 0 ? $pagination->getPerPage() : static::ITEMS_COUNT_DEFAULT;

        if ($pagination->getPerPage() > static::ITEMS_COUNT_MAX) {
            $limit = static::ITEMS_COUNT_MAX;
        }

        if ($pagination->getPerPage() <= 0) {
            $limit = static::ITEMS_COUNT_DEFAULT;
        }

        $offset = 0;

        if ($pagination->getPage() > 1) {
            $offset = $limit * ($pagination->getPage() - 1);
        }

        return [$limit, $offset];
    }

    /**
     * @return array<string>
     */
    protected function getSortList(): array
    {
        $sortBy = [];

        foreach ($this->getReportRequest()->getRequestDto()->getSort() as $sortColumn) {
            $trimmedSortColumn = ltrim($sortColumn, '-');

            if (in_array($trimmedSortColumn, $this->getSortColumns(), true)) {
                $operator = $sortColumn[0] === '-' ? 'DESC' : 'ASC';
                $sortBy[] = sprintf('%s %s', $trimmedSortColumn, $operator);
            }
        }

        return $sortBy;
    }
}
