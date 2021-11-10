<?php

declare(strict_types=1);

namespace App\DTO\QueryBuilder;

final class AssignCondition
{
    /**
     * @var array<string>
     */
    private array $assignExpressions;

    /**
     * @var array<string, string|int|bool|array>
     */
    private array $placeholders;

    /**
     * @param array<string> $assignExpressions
     */
    public function setAssignExpressions(array $assignExpressions): void
    {
        $this->assignExpressions = $assignExpressions;
    }

    /**
     * @param array<string, string|int|bool> $placeholders
     */
    public function setPlaceholders(array $placeholders): void
    {
        $this->placeholders = $placeholders;
    }

    /**
     * @return array<string>
     */
    public function getAssignExpressions(): array
    {
        return $this->assignExpressions;
    }

    public function getAssignExpressionsAsString(): string
    {
        return '(' . implode(') AND (', $this->assignExpressions) . ')';
    }

    public function addAssignExpression(string $assignExpression): void
    {
        $this->assignExpressions[] = $assignExpression;
    }

    /**
     * @return array<string, string|int|bool|array>
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    public function addPlaceholder(string $placeholder, string|int|bool|array $value): void
    {
        $this->placeholders[$placeholder] = $value;
    }
}
