<?php

declare(strict_types=1);

namespace App\DTO\Response;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ReportResponseItem",
 *     description="Report Response Object",
 *     required={"id", "type", "attributes"}
 * )
 */
class ReportResponseItem
{
    private const TYPE_DEFAULT = 'row';

    private string $id = '';

    private string $type = self::TYPE_DEFAULT;

    /**
     * @OA\Property(
     *     type="object",
     *     additionalProperties=@OA\AdditionalProperties(type="string")
     * )
     * @var array<string, string>
     */
    private array $attributes = [];

    /**
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array<string, string> $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
