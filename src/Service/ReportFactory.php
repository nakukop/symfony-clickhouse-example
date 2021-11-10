<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ReportNoFoundGeneralException;
use App\Service\Report\ReportInterface;

class ReportFactory
{
    public const SERVICE_TAG = 'report_service';

    /**
     * @var array<ReportInterface>
     */
    private array $reports = [];

    public function addReport(ReportInterface $report): void
    {
        $this->reports[$report::class] = $report;
    }

    public function getReport(string $className): ReportInterface
    {
        if (!isset($this->reports[$className])) {
            throw new ReportNoFoundGeneralException(sprintf('Unknown report "%s"', $className));
        }

        return $this->reports[$className];
    }
}
