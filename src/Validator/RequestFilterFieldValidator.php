<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\Request\RequestFilterField;
use Decimal\Decimal;
use DomainException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RequestFilterFieldValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RequestFilterFieldConstraint) {
            throw new UnexpectedTypeException($constraint, RequestFilterFieldConstraint::class);
        }

        if (!$value instanceof RequestFilterField) {
            throw new UnexpectedTypeException($value, RequestFilterField::class);
        }

        $this->validateByFieldType($value, $constraint);
        $this->validateByFieldTypesCompatibility($value);
        $this->validateByFieldValuesType($value, $constraint);
    }

    private function validateByFieldType(RequestFilterField $value, RequestFilterFieldConstraint $constraint): void
    {
        if (
            $value->getValues() !== []
            && !in_array(RequestFilterField::FIELD_VALUES, $constraint->getAllowedFieldTypes(), true)
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ type }}', RequestFilterField::FIELD_VALUES)
                ->setParameter('{{ field }}', $value->getId())
                ->addViolation();
        }

        foreach (
            [
                RequestFilterField::FIELD_GREAT_OR_EQUAL => $value->getGreatOrEqual(),
                RequestFilterField::FIELD_GREAT => $value->getGreat(),
                RequestFilterField::FIELD_LESS_OR_EQUAL => $value->getLessOrEqual(),
                RequestFilterField::FIELD_LESS => $value->getLess(),
                RequestFilterField::FIELD_EQUAL => $value->getEqual(),
                RequestFilterField::FIELD_NOT_EQUAL => $value->getNotEqual(),
            ] as $compareFieldName => $compareFieldValue
        ) {
            if (
                $compareFieldValue !== ''
                && !in_array($compareFieldName, $constraint->getAllowedFieldTypes(), true)
            ) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ type }}', $compareFieldName)
                    ->setParameter('{{ field }}', $value->getId())
                    ->addViolation();
            }
        }

        if (
            $value->getValue() !== ''
            && !in_array(RequestFilterField::FIELD_VALUE, $constraint->getAllowedFieldTypes(), true)
        ) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ type }}', RequestFilterField::FIELD_VALUE)
                ->setParameter('{{ field }}', $value->getId())
                ->addViolation();
        }
    }

    private function validateByFieldValuesType(
        RequestFilterField $value,
        RequestFilterFieldConstraint $constraint,
    ): void {
        $types = $constraint->getAllowedValuesTypes();
        $valuesList = [
            RequestFilterField::FIELD_VALUE => $value->getValue(),
            RequestFilterField::FIELD_EQUAL => $value->getEqual(),
            RequestFilterField::FIELD_NOT_EQUAL => $value->getNotEqual(),
            RequestFilterField::FIELD_GREAT_OR_EQUAL => $value->getGreatOrEqual(),
            RequestFilterField::FIELD_GREAT => $value->getGreat(),
            RequestFilterField::FIELD_LESS_OR_EQUAL => $value->getLessOrEqual(),
            RequestFilterField::FIELD_LESS => $value->getLess(),
        ];

        foreach (array_merge($valuesList, $value->getValues()) as $key => $val) {
            if (is_int($key)) {
                $key = sprintf('%s[%d]', RequestFilterField::FIELD_VALUES, $key);
            }

            if ($val === null || $val === '') {
                continue;
            }

            if (is_array($val) || is_object($val)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ type }}', 'array/object')
                    ->setParameter('{{ field }}', $value->getId())
                    ->addViolation();
            }

            /** @psalm-suppress RedundantCast */
            if (
                (!is_numeric($val) || str_contains((string)$val, '.'))
                && in_array(RequestFilterFieldConstraint::VALUES_TYPE_INTEGER, $types, true)
            ) {
                $this->context->buildViolation(sprintf('%s in filter \'%s\'', $constraint->message, $key))
                    ->setParameter('{{ type }}', 'not ' . RequestFilterFieldConstraint::VALUES_TYPE_INTEGER)
                    ->setParameter('{{ field }}', $value->getId())
                    ->addViolation();
            }

            if (
                in_array(RequestFilterFieldConstraint::VALUES_TYPE_UUID, $types, true)
                && uuid_is_valid($val) === false
            ) {
                $this->context->buildViolation(sprintf('%s in filter \'%s\'', $constraint->message, $key))
                    ->setParameter('{{ type }}', 'not ' . RequestFilterFieldConstraint::VALUES_TYPE_UUID)
                    ->setParameter('{{ field }}', $value->getId())
                    ->addViolation();
            }

            if (
                in_array(RequestFilterFieldConstraint::VALUES_TYPE_BOOLEAN, $types, true)
                && $val !== ''
                && !in_array(
                    $val,
                    ['1', '0', true, false],
                    true,
                )
            ) {
                $this->context->buildViolation(sprintf('%s in filter \'%s\'', $constraint->message, $key))
                    ->setParameter('{{ type }}', 'not ' . RequestFilterFieldConstraint::VALUES_TYPE_BOOLEAN)
                    ->setParameter('{{ field }}', $value->getId())
                    ->addViolation();
            }

            if (in_array(RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL, $types, true)) {
                if (!is_numeric($val)) {
                    $isCorrect = false;
                } else {
                    try {
                        $isCorrect = is_numeric((new Decimal($val))->toString());
                    } catch (DomainException) {
                        $isCorrect = false;
                    }
                }

                if (!$isCorrect) {
                    $this->context->buildViolation(sprintf('%s in filter \'%s\'', $constraint->message, $key))
                        ->setParameter('{{ type }}', 'not ' . RequestFilterFieldConstraint::VALUES_TYPE_DECIMAL)
                        ->setParameter('{{ field }}', $value->getId())
                        ->addViolation();
                }
            }
        }
    }

    private function validateByFieldTypesCompatibility(RequestFilterField $value): void
    {
        $incompatibleMap = [
            RequestFilterField::FIELD_GREAT_OR_EQUAL => [
                RequestFilterField::FIELD_GREAT,
                RequestFilterField::FIELD_EQUAL,
            ],
            RequestFilterField::FIELD_GREAT => [
                RequestFilterField::FIELD_GREAT_OR_EQUAL,
                RequestFilterField::FIELD_EQUAL,
            ],
            RequestFilterField::FIELD_LESS_OR_EQUAL => [
                RequestFilterField::FIELD_LESS,
                RequestFilterField::FIELD_EQUAL,
            ],
            RequestFilterField::FIELD_LESS => [
                RequestFilterField::FIELD_LESS_OR_EQUAL,
                RequestFilterField::FIELD_EQUAL,
            ],
            RequestFilterField::FIELD_EQUAL => [
                RequestFilterField::FIELD_GREAT,
                RequestFilterField::FIELD_GREAT_OR_EQUAL,
                RequestFilterField::FIELD_LESS,
                RequestFilterField::FIELD_LESS_OR_EQUAL,
                RequestFilterField::FIELD_NOT_EQUAL,
            ],
            RequestFilterField::FIELD_NOT_EQUAL => [
                RequestFilterField::FIELD_EQUAL,
            ],
        ];

        $compareFieldsValues = [
            RequestFilterField::FIELD_GREAT_OR_EQUAL => $value->getGreatOrEqual(),
            RequestFilterField::FIELD_GREAT => $value->getGreat(),
            RequestFilterField::FIELD_LESS_OR_EQUAL => $value->getLessOrEqual(),
            RequestFilterField::FIELD_LESS => $value->getLess(),
            RequestFilterField::FIELD_EQUAL => $value->getEqual(),
            RequestFilterField::FIELD_NOT_EQUAL => $value->getNotEqual(),
        ];

        $conflictFields = [];

        foreach ($compareFieldsValues as $checkedType => $checkedValue) {
            if ($checkedValue === '' || !isset($incompatibleMap[$checkedType])) {
                continue;
            }

            foreach ($incompatibleMap[$checkedType] as $incompatibleType) {
                if ($compareFieldsValues[$incompatibleType] === '') {
                    continue;
                }

                $conflictFields[] = $checkedType;
            }
        }

        if ($conflictFields === []) {
            return;
        }

        $this->context
            ->buildViolation(sprintf(
                'Types conflict for field %s: %s',
                $value->getId(),
                implode(', ', array_unique($conflictFields)),
            ))
            ->addViolation();
    }
}
