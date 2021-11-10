<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

class ReportListMetaInfo
{
    /**
     * @var array<ReportListMetaInfoItem>
     */
    private array $data;

    /**
     * @param array<ReportListMetaInfoItem>|null $data
     */
    public function __construct(?array $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @return array<ReportListMetaInfoItem>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<ReportListMetaInfoItem> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
