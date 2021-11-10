<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ReportNoFoundGeneralException;
use App\Service\Report\Gambling\GamblingSessionsReport;
use App\Service\Report\ReportInterface;
use Symfony\Component\HttpFoundation\Request;

class ReportBuilderService
{
    private const MAP = [
        ReportRequestBuilderService::REPORT_GAMBLING_SESSION_LOG => GamblingSessionsReport::class,
    ];

    public function __construct(private ReportFactory $reportFactory)
    {
    }

    public function buildReport(Request $request): ReportInterface
    {
        return $this
            ->reportFactory
            ->getReport($this->getReportClassName($request));
    }

    private function getReportClassName(Request $request): string
    {
        $type = $request->get(ReportRequestBuilderService::REPORTS_IDENTIFIER_PARAM);

        if (!array_key_exists($type, self::MAP)) {
            throw new ReportNoFoundGeneralException('Report type `' . $type . '` is not found.');
        }

        return self::MAP[$type];
    }
}
