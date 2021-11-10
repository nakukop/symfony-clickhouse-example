<?php

declare(strict_types=1);

namespace App\DTO\Request;

use App\Service\RequestFilter\RequestFilterMapperInterface;

final class ReportRequest
{
    public function __construct(private RequestFilterMapperInterface $filter, private ReportRequestDto $requestDto)
    {
    }

    public function getFilter(): RequestFilterMapperInterface
    {
        return $this->filter;
    }

    public function setFilter(RequestFilterMapperInterface $filter): void
    {
        $this->filter = $filter;
    }

    public function getRequestDto(): ReportRequestDto
    {
        return $this->requestDto;
    }

    public function setRequestDto(ReportRequestDto $requestDto): void
    {
        $this->requestDto = $requestDto;
    }
}
