<?php

declare(strict_types=1);

namespace App\Validator;

use App\DTO\Request\RequestFilterField;
use Attribute;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class RequestFilterFieldConstraint extends Constraint
{
    public const KEY_ALLOWED_FIELDS_TYPES = 'allowedFieldTypes';

    public const KEY_ALLOWED_VALUES_TYPES = 'allowedValuesTypes';

    public const VALUES_TYPE_INTEGER = 'integer';

    public const VALUES_TYPE_STRING = 'string';

    public const VALUES_TYPE_UUID = 'uuid';

    public const VALUES_TYPE_DECIMAL = 'decimal';

    public const VALUES_TYPE_BOOLEAN = 'boolean';

    public string $message = 'Invalid \'{{ type }}\' type for field \'{{ field }}\'';

    /**
     * @var array<string>
     */
    public array $allowedFieldTypes = [];

    /**
     * @var array<string>
     */
    public array $allowedValuesTypes = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->setAllowedFieldTypes($options[self::KEY_ALLOWED_FIELDS_TYPES] ?? []);
        $this->setAllowedValuesTypes($options[self::KEY_ALLOWED_VALUES_TYPES] ?? []);
    }

    public function validatedBy(): string
    {
        return RequestFilterFieldValidator::class;
    }

    /**
     * @return array<string>
     */
    public function getAllowedFieldTypes(): array
    {
        return $this->allowedFieldTypes;
    }

    /**
     * @return array<string>
     */
    public function getAllowedValuesTypes(): array
    {
        return $this->allowedValuesTypes;
    }

    /**
     * @param array<string> $allowedTypes
     */
    private function setAllowedFieldTypes(array $allowedTypes): void
    {
        foreach ($allowedTypes as $allowedType) {
            if (!in_array($allowedType, $this->getAllowedFieldTypesList(), true)) {
                throw new InvalidArgumentException('Invalid type: ' . $allowedType);
            }
        }

        $this->allowedFieldTypes = $allowedTypes;
    }

    /**
     * @return array<string>
     */
    private function getAllowedFieldTypesList(): array
    {
        return [
            RequestFilterField::FIELD_VALUE,
            RequestFilterField::FIELD_VALUES,
            RequestFilterField::FIELD_EQUAL,
            RequestFilterField::FIELD_NOT_EQUAL,
            RequestFilterField::FIELD_GREAT,
            RequestFilterField::FIELD_GREAT_OR_EQUAL,
            RequestFilterField::FIELD_LESS,
            RequestFilterField::FIELD_LESS_OR_EQUAL,
        ];
    }

    /**
     * @param array<string> $allowedTypes
     */
    private function setAllowedValuesTypes(array $allowedTypes): void
    {
        foreach ($allowedTypes as $allowedType) {
            if (!in_array($allowedType, $this->getAllowedValuesTypesList(), true)) {
                throw new InvalidArgumentException('Invalid values type: ' . $allowedType);
            }
        }

        $this->allowedValuesTypes = $allowedTypes;
    }

    /**
     * @return array<string>
     */
    private function getAllowedValuesTypesList(): array
    {
        return [
            self::VALUES_TYPE_INTEGER,
            self::VALUES_TYPE_DECIMAL,
            self::VALUES_TYPE_STRING,
            self::VALUES_TYPE_UUID,
            self::VALUES_TYPE_BOOLEAN,
        ];
    }
}
