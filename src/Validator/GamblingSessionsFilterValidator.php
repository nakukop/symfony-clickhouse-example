<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\Request\RequestFilterField;
use App\Service\RequestFilter\Gambling\GamblingSessionsRequestFilter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GamblingSessionsFilterValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof GamblingSessionsFilterConstraint) {
            throw new UnexpectedTypeException($constraint, GamblingSessionsFilterConstraint::class);
        }

        if (!$value instanceof GamblingSessionsRequestFilter) {
            throw new UnexpectedTypeException($value, GamblingSessionsRequestFilter::class);
        }

        if ($this->isEmptyAllFields($value->getDateActive(), $value->getDateStart(), $value->getDateEnd())) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter(
                    '{{ fields }}',
                    'dateActive, dateStart, dateEnd',
                )
                ->addViolation();
        }
    }

    private function isEmptyAllFields(?RequestFilterField ...$dateFields): bool
    {
        $isFilled = false;

        foreach ($dateFields as $field) {
            if ($field === null) {
                continue;
            }

            if (
                $field->getValue() !== ''
                || $field->getEqual() !== ''
                || $field->getNotEqual() !== ''
                || $field->getLess() !== ''
                || $field->getLessOrEqual() !== ''
                || $field->getGreat() !== ''
                || $field->getGreatOrEqual() !== ''
                || $field->getValues() !== []
            ) {
                $isFilled = true;

                break;
            }
        }

        return !$isFilled;
    }
}
