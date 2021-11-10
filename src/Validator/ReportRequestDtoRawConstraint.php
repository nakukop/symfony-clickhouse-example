<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ReportRequestDtoRawConstraint extends Constraint
{
    public function validatedBy(): string
    {
        return ReportRequestDtoRawValidator::class;
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
