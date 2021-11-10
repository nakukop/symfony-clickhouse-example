<?php

declare(strict_types=1);

namespace App\Exception;

use B2B\ErrorHandleBundle\Exception\ValidationExceptionInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ReportFilterException extends Exception implements Throwable, ValidationExceptionInterface
{
    /**
     * @param array<string, array<string>> $validationErrors
     */
    public function __construct(private array $validationErrors)
    {
        parent::__construct('Validation error.', JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @inheritDoc
     */
    public function getValidationErrors(): array
    {
        /** @psalm-suppress */
        return $this->validationErrors;
    }
}
