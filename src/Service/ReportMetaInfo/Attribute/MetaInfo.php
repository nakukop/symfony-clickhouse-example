<?php

declare(strict_types=1);

namespace App\Service\ReportMetaInfo\Attribute;

use App\DTO\Request\RequestFilterField;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MetaInfo
{
    public const TYPE_SELECT = 'select';

    public const TYPE_COMPARE = 'compare';

    public const TYPE_INPUT = 'input';

    public const TYPE_CHECKBOX = 'checkbox';

    public const VALUE_TYPE_INT = 'integer';

    public const VALUE_TYPE_DECIMAL = 'decimal';

    public const VALUE_TYPE_STRING = 'string';

    public const VALUE_TYPE_UUID = 'uuid';

    public const VALUE_TYPE_BOOL = 'boolean';

    public const VALUE_TYPE_DATE = 'date';

    public const VALUE_TYPE_DATE_TIME = 'dateTime';

    public const COMPARE_MODE_EQUAL = RequestFilterField::FIELD_EQUAL;

    public const COMPARE_MODE_NOT_EQUAL = RequestFilterField::FIELD_NOT_EQUAL;

    public const COMPARE_MODE_GREAT = RequestFilterField::FIELD_GREAT;

    public const COMPARE_MODE_GREAT_OR_EQUAL = RequestFilterField::FIELD_GREAT_OR_EQUAL;

    public const COMPARE_MODE_LESS = RequestFilterField::FIELD_LESS;

    public const COMPARE_MODE_LESS_OR_EQUAL = RequestFilterField::FIELD_LESS_OR_EQUAL;

    public const COMPARE_MODES_ALL = [
        self::COMPARE_MODE_EQUAL,
        //self::COMPARE_MODE_NOT_EQUAL,
        //self::COMPARE_MODE_GREAT,
        self::COMPARE_MODE_GREAT_OR_EQUAL,
        //self::COMPARE_MODE_LESS,
        self::COMPARE_MODE_LESS_OR_EQUAL,
    ];

    public function __construct(
        private string $id,
        private string $type,
        private string $title,
        private bool $isRequired,
        private bool $isMulti,
        private string $valueType,
        private array|null $compareModes = null,
        private string|int|float|bool|null $defaultValue = null,
        private string|null $dictionary = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function isMulti(): bool
    {
        return $this->isMulti;
    }

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function getCompareModes(): array|null
    {
        return $this->compareModes;
    }

    public function getDefaultValue(): float|bool|int|string|null
    {
        return $this->defaultValue;
    }

    public function getDictionary(): string|null
    {
        return $this->dictionary;
    }
}
