<?php

declare(strict_types=1);

namespace App\Service\ReportMetaInfo;

use App\DTO\MetaInfo\ReportListMetaInfo;
use App\DTO\MetaInfo\ReportListMetaInfoItem;
use App\DTO\MetaInfo\ReportMetaInfoColumnAttribute;
use App\DTO\MetaInfo\ReportMetaInfoColumnItem;
use App\DTO\MetaInfo\ReportMetaInfoColumns;
use App\DTO\MetaInfo\ReportMetaInfoFilter;
use App\DTO\MetaInfo\ReportMetaInfoFilterItem;
use App\DTO\MetaInfo\ReportMetaInfoFilterItemAttribute;
use App\Service\ReportBuilderService;
use App\Service\ReportMetaInfo\Attribute\MetaInfo;
use App\Service\ReportRequestBuilderService;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

class ReportMetaInfoService
{
    public function __construct(
        private ReportRequestBuilderService $requestBuilderService,
        private DictionaryDataProvider $dictionaryDataProvider,
        private ReportBuilderService $reportBuilderService,
    ) {
    }

    public function getMetaInfoFilter(Request $request): ReportMetaInfoFilter
    {
        $filterReflector = new ReflectionClass($this->requestBuilderService->getFilter($request));

        $data = [];

        foreach ($filterReflector->getProperties() as $propertyReflector) {
            foreach ($propertyReflector->getAttributes(MetaInfo::class) as $metaInfoAttribute) {
                $metaInfoReader = $metaInfoAttribute->newInstance();

                assert($metaInfoReader instanceof MetaInfo);

                $itemAttributesDto = new ReportMetaInfoFilterItemAttribute();
                $itemAttributesDto->setValueType($metaInfoReader->getValueType());
                $itemAttributesDto->setTitle($metaInfoReader->getTitle());
                $itemAttributesDto->setIsMulti($metaInfoReader->isMulti());
                $itemAttributesDto->setIsRequired($metaInfoReader->isRequired());

                if ($metaInfoReader->getDefaultValue() !== null) {
                    $itemAttributesDto->setDefaultValue($metaInfoReader->getDefaultValue());
                }

                if ($metaInfoReader->getCompareModes() !== null) {
                    $itemAttributesDto->setCompareModes($metaInfoReader->getCompareModes());
                }

                if ($metaInfoReader->getDictionary() !== null) {
                    $itemAttributesDto->setDictionary(
                        $this->dictionaryDataProvider
                            ->getDictionary($metaInfoReader->getDictionary()),
                    );
                }

                $metaInfoItem = new ReportMetaInfoFilterItem();
                $metaInfoItem->setId($metaInfoReader->getId());
                $metaInfoItem->setType($metaInfoReader->getType());
                $metaInfoItem->setAttributes($itemAttributesDto);

                $data[] = $metaInfoItem;
            }
        }

        return new ReportMetaInfoFilter($data);
    }

    public function getMetaInfoColumns(Request $request): ReportMetaInfoColumns
    {
        $report = $this->reportBuilderService->buildReport($request);

        $data = [];

        foreach ($report->getAllColumns() as $columnName) {
            $columnAttributes = new ReportMetaInfoColumnAttribute();
            $columnAttributes->setTitle(ucfirst(preg_replace(
                '/([A-Z])/',
                ' ${1}',
                $columnName,
            )));
            $columnAttributes->setIsSortable(in_array($columnName, $report->getSortColumns(), true));
            $columnAttributes->setIsShowByDefault(in_array($columnName, $report->getDefaultColumns(), true));

            $column = new ReportMetaInfoColumnItem();
            $column->setId($columnName);
            $column->setAttributes($columnAttributes);

            $data[] = $column;
        }

        return new ReportMetaInfoColumns($data);
    }

    public function getAllReportsInfo(): ReportListMetaInfo
    {
        $data = [];

        foreach ($this->requestBuilderService->getReportIdentifiersWithCategories() as $id => $category) {
            $item = new ReportListMetaInfoItem();
            $item->setId($id);
            $item->setCategory($category);
            $data[] = $item;
        }

        return new ReportListMetaInfo($data);
    }
}
