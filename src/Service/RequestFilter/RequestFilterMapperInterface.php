<?php

declare(strict_types=1);

namespace App\Service\RequestFilter;

use App\DTO\Request\ReportRequestDto;

interface RequestFilterMapperInterface
{
    /**
     * Method returns a map of fields set. For example:
     * [
     *      'requestDtoFilterNameFirst' => ['thisSetterNameFirst' => ['foo', $bar, new Baz()]],
     *      $requestDto->getFilters[2]->getName() => ['thisSetterNameSecond' => $setterMethodParams],
     * ]
     *
     * @return array<string, array<string, array<string|int|bool>>>
     */
    public function getMap(ReportRequestDto $requestDto): array;
}
