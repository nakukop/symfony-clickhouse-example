<?php

declare(strict_types=1);

namespace App\DTO\MetaInfo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportMetaInfoFilterItemAttribute",
 *     description="Object of attribute for list in ReportMetaInfoFilterItem",
 *     required={"title", "type", "required", "multi"}
 * )
 */
class ReportMetaInfoFilterItemAttribute
{
    private string $title = '';

    private string $valueType = '';

    private string|int|float|bool|null $defaultValue;

    /**
     * @OA\Property(type="boolean", property="required")
     */
    private bool $isRequired = false;

    /**
     * @OA\Property(type="boolean", property="multi")
     */
    private bool $isMulti = false;

    /**
     * @var array<string>|null
     */
    private array|null $compareModes = null;

    /**
     * @var array<ReportMetaInfoDictionaryItem>|null
     */
    private array|null $dictionary = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): void
    {
        $this->valueType = $valueType;
    }

    public function getDefaultValue(): bool|int|float|string|null
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(bool|int|float|string|null $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getIsRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    public function getIsMulti(): bool
    {
        return $this->isMulti;
    }

    public function setIsMulti(bool $isMulti): void
    {
        $this->isMulti = $isMulti;
    }

    /**
     * @return array<string>|null
     */
    public function getCompareModes(): array|null
    {
        return $this->compareModes;
    }

    /**
     * @param array<string>|null $compareModes
     */
    public function setCompareModes(array|null $compareModes): void
    {
        $this->compareModes = $compareModes;
    }

    /**
     * @return array<ReportMetaInfoDictionaryItem>|null
     */
    public function getDictionary(): array|null
    {
        return $this->dictionary;
    }

    /**
     * @param array<ReportMetaInfoDictionaryItem>|null $dictionary
     */
    public function setDictionary(array|null $dictionary): void
    {
        $this->dictionary = $dictionary;
    }
}
