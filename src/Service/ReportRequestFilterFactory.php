<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ReportNoFoundGeneralException;
use App\Service\RequestFilter\RequestFilterMapperInterface;

class ReportRequestFilterFactory
{
    public const SERVICE_TAG = 'report_request_filter_service';

    /**
     * @var array<RequestFilterMapperInterface>
     */
    private array $filters = [];

    public function addFilter(RequestFilterMapperInterface $filter): void
    {
        $this->filters[$filter::class] = $filter;
    }

    public function getFilter(string $className): RequestFilterMapperInterface
    {
        if (!isset($this->filters[$className])) {
            throw new ReportNoFoundGeneralException(sprintf('Unknown request filter "%s"', $className));
        }

        return $this->filters[$className];
    }
}
