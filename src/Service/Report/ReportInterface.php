<?php

declare(strict_types=1);

namespace App\Service\Report;

use App\DTO\Request\ReportRequest;
use App\DTO\Response\ReportResponse;

interface ReportInterface
{
    /**
     * @return array<string>
     */
    public function getRequiredColumns(): array;

    /**
     * @return array<string>
     */
    public function getDefaultColumns(): array;

    /**
     * @return array<string>
     */
    public function getAllColumns(): array;

    /**
     * @return array<string>
     */
    public function getSortColumns(): array;

    /**
     * @return array<string>
     */
    public function getGroupByColumns(): array;

    public function prepareStatement(): ReportInterface;

    public function setReportRequest(ReportRequest $reportRequest): ReportInterface;

    public function getReportResponse(): ReportResponse;
}
