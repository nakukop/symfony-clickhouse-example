<?php

declare(strict_types=1);

namespace App\DTO\Request;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="RequestFilterField",
 *     description="Request Filter Field Object",
 *     required={"name"}
 * )
 */
class RequestFilterField
{
    public const FIELD_ID = 'id';

    public const FIELD_VALUE = 'value';

    public const FIELD_VALUES = 'values';

    public const FIELD_GREAT_OR_EQUAL = 'greatOrEqual';

    public const FIELD_GREAT = 'great';

    public const FIELD_LESS_OR_EQUAL = 'lessOrEqual';

    public const FIELD_LESS = 'less';

    public const FIELD_EQUAL = 'equal';

    public const FIELD_NOT_EQUAL = 'notEqual';

    private string $id = '';

    private string $value = '';

    /**
     * @var array<int, string>
     */
    private array $values = [];

    private string $greatOrEqual = '';

    private string $great = '';

    private string $lessOrEqual = '';

    private string $less = '';

    private string $equal = '';

    private string $notEqual = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return array<int, string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<int, string> $values
     */
    public function setValues(array $values): void
    {
        $this->values = $values;
    }

    public function getGreat(): string
    {
        return $this->great;
    }

    public function setGreat(string $great): void
    {
        $this->great = $great;
    }

    public function getLess(): string
    {
        return $this->less;
    }

    public function setLess(string $less): void
    {
        $this->less = $less;
    }

    public function getEqual(): string
    {
        return $this->equal;
    }

    public function setEqual(string $equal): void
    {
        $this->equal = $equal;
    }

    public function getNotEqual(): string
    {
        return $this->notEqual;
    }

    public function setNotEqual(string $notEqual): void
    {
        $this->notEqual = $notEqual;
    }

    public function getGreatOrEqual(): string
    {
        return $this->greatOrEqual;
    }

    public function setGreatOrEqual(string $greatOrEqual): void
    {
        $this->greatOrEqual = $greatOrEqual;
    }

    public function getLessOrEqual(): string
    {
        return $this->lessOrEqual;
    }

    public function setLessOrEqual(string $lessOrEqual): void
    {
        $this->lessOrEqual = $lessOrEqual;
    }
}
