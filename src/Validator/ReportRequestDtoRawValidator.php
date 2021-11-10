<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\Request\ReportRequestDto;
use App\DTO\Request\RequestFilterField;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReportRequestDtoRawValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof ReportRequestDtoRawConstraint) {
            throw new UnexpectedTypeException($constraint, ReportRequestDtoRawConstraint::class);
        }

        if (!$value instanceof ReportRequestDto) {
            throw new UnexpectedTypeException($value, ReportRequestDto::class);
        }

        $fields = [
            ReportRequestDto::REPORT_REQUEST_KEY_FILTERS,
            ReportRequestDto::REPORT_REQUEST_KEY_SHOW_COLUMNS,
            ReportRequestDto::REPORT_REQUEST_KEY_SORT,
            ReportRequestDto::REPORT_REQUEST_KEY_PAGINATION,
        ];

        $filterKeys = [
            RequestFilterField::FIELD_ID,
            RequestFilterField::FIELD_VALUE,
            RequestFilterField::FIELD_VALUES,
            RequestFilterField::FIELD_EQUAL,
            RequestFilterField::FIELD_NOT_EQUAL,
            RequestFilterField::FIELD_GREAT_OR_EQUAL,
            RequestFilterField::FIELD_GREAT,
            RequestFilterField::FIELD_LESS_OR_EQUAL,
            RequestFilterField::FIELD_LESS,
        ];

        foreach ($value->getDataRawRequest() as $fieldName => $field) {
            if (!in_array($fieldName, $fields, true)) {
                $this->context->buildViolation('Field is not supported.')->atPath($fieldName)->addViolation();
            }

            if ($fieldName === ReportRequestDto::REPORT_REQUEST_KEY_PAGINATION) {
                foreach ($field as $fieldDataName => $fieldDataVal) {
                    $paginatorKeyList = [
                        ReportRequestDto::PAGINATION_PAGE_KEY,
                        ReportRequestDto::PAGINATION_PER_PAGE_KEY,
                    ];

                    if (
                        !in_array($fieldDataName, $paginatorKeyList, true)
                        || !is_numeric($fieldDataVal)
                    ) {
                        $this->context
                            ->buildViolation('Field ' . $fieldName . ' must be object {page: <int>, perPage: <int>}')
                            ->atPath($fieldName)->addViolation();

                        continue;
                    }

                    if (
                        $fieldDataName === ReportRequestDto::PAGINATION_PER_PAGE_KEY
                        && (
                            (int)$fieldDataVal > ReportRequestDto::PAGINATION_PER_PAGE_VALUE_MAX
                            || (int)$fieldDataVal < 0
                        )
                    ) {
                        $message = sprintf(
                            'Field %s must have %s value: from 0 to %d',
                            $fieldName,
                            ReportRequestDto::PAGINATION_PER_PAGE_KEY,
                            ReportRequestDto::PAGINATION_PER_PAGE_VALUE_MAX,
                        );

                        $this->context->buildViolation($message)->atPath($fieldName)->addViolation();
                    }

                    if (
                        $fieldDataName === ReportRequestDto::PAGINATION_PAGE_KEY
                        && (int)$fieldDataVal < 0
                    ) {
                        $message = sprintf(
                            'Field %s must have %s value greater than 0',
                            $fieldName,
                            ReportRequestDto::PAGINATION_PAGE_KEY,
                        );
                        $this->context->buildViolation($message)->atPath($fieldName)->addViolation();
                    }
                }
            }

            if (
                $fieldName === ReportRequestDto::REPORT_REQUEST_KEY_SORT
                || $fieldName === ReportRequestDto::REPORT_REQUEST_KEY_SHOW_COLUMNS
            ) {
                if (!is_array($field)) {
                    $this->context->buildViolation('Field ' . $fieldName . ' must be array<int, string>')
                        ->atPath($fieldName)->addViolation();

                    return;
                }

                foreach ($field as $fieldDataIndex => $fieldDataVal) {
                    if (!is_numeric($fieldDataIndex) || !is_string($fieldDataVal)) {
                        $this->context->buildViolation('Field ' . $fieldName . ' must be array<int, string>')
                            ->atPath($fieldName)->addViolation();
                    }
                }
            }

            if ($fieldName === ReportRequestDto::REPORT_REQUEST_KEY_FILTERS) {
                foreach ($field as $fieldData) {
                    if (!isset($fieldData[RequestFilterField::FIELD_ID])) {
                        $this->context
                            ->buildViolation('Every item for filter must have `id` key')
                            ->atPath(ReportRequestDto::REPORT_REQUEST_KEY_FILTERS)
                            ->addViolation();
                    }

                    foreach ($fieldData as $fieldDataKey => $fieldDataValue) {
                        if (!in_array($fieldDataKey, $filterKeys, true)) {
                            $this->context
                                ->buildViolation(sprintf(
                                    'Invalid filter key: %s',
                                    (string)$fieldDataKey,
                                ))
                                ->atPath(ReportRequestDto::REPORT_REQUEST_KEY_FILTERS)
                                ->addViolation();
                        }

                        if (
                            ($fieldDataValue === null || is_array($fieldDataValue) || is_object($fieldDataValue))
                            && $fieldDataKey !== RequestFilterField::FIELD_VALUES
                        ) {
                            $this->context->buildViolation('Invalid filter value type: ' . (string)$fieldDataKey)
                                ->atPath(ReportRequestDto::REPORT_REQUEST_KEY_FILTERS)
                                ->addViolation();
                        }

                        if (
                            !is_array($fieldDataValue)
                            && $fieldDataKey === RequestFilterField::FIELD_VALUES
                        ) {
                            $this->context
                                ->buildViolation('Invalid filter value type: ' . RequestFilterField::FIELD_VALUES)
                                ->atPath(ReportRequestDto::REPORT_REQUEST_KEY_FILTERS)
                                ->addViolation();
                        }
                    }
                }
            }
        }
    }
}
