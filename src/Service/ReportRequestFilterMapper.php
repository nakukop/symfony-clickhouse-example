<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\ReportRequestDto;
use App\Service\RequestFilter\RequestFilterMapperInterface;

class ReportRequestFilterMapper
{
    /**
     * @return array<string>
     */
    public function mapRequestDto(
        RequestFilterMapperInterface $filter,
        ReportRequestDto $requestDto
    ): array {
        $map = $filter->getMap($requestDto);
        $unmappedFieldsNames = [];

        foreach ($requestDto->getFilters() as $filterField) {
            $setterName = $map === []
                ? sprintf('set%s', ucfirst($filterField->getId()))
                : array_key_first($map[$filterField->getId()]);
            $params = $map === []
                ? [$filterField]
                : $map[$filterField->getId()][$setterName];

            if (method_exists($filter, $setterName)) {
                $filter->{$setterName}(...$params);

                continue;
            }

            $unmappedFieldsNames[] = $filterField->getId();
        }

        return $unmappedFieldsNames;
    }
}
