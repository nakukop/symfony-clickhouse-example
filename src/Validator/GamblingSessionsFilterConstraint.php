<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class GamblingSessionsFilterConstraint extends Constraint
{
    public string $message = 'At least one of the filter\'s fields must be filled: {{ fields }}';

    public function validatedBy(): string
    {
        return GamblingSessionsFilterValidator::class;
    }

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
