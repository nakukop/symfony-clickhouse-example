<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\QueryBuilder\AssignCondition;
use App\DTO\Request\ReportRequest;
use App\DTO\Request\RequestFilterField;

class ReportQueryBuilder
{
    private string $from;

    /**
     * @var array<string>
     */
    private array $selectList = [];

    /**
     * @var array<string>
     */
    private array $joinList = [];

    /**
     * @var array<string>
     */
    private array $whereList = [];

    /**
     * @var array<string>
     */
    private array $groupList = [];

    /**
     * @var array<string>
     */
    private array $groupExclusionList = [];

    /**
     * @var array<string>
     */
    private array $sortList = [];

    /**
     * @var array<string>
     */
    private array $havingList = [];

    /**
     * @var array<string, int|string|bool|array>
     */
    private array $parameterList = [];

    private int $limit = 0;

    private int $offset = 0;

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * @return array<string>
     */
    public function getSelectList(): array
    {
        return array_unique($this->selectList);
    }

    /**
     * @param array<string> $selectList
     */
    public function setSelectList(array $selectList): void
    {
        $this->selectList = $selectList;
    }

    public function addToSelectList(string $select): void
    {
        $this->selectList[] = $select;
    }

    /**
     * @return array<string>
     */
    public function getJoinList(): array
    {
        return array_unique($this->joinList);
    }

    /**
     * @param array<string> $joinList
     */
    public function setJoinList(array $joinList): void
    {
        $this->joinList = $joinList;
    }

    public function addToJoinList(string $join): void
    {
        $this->joinList[] = $join;
    }

    /**
     * @return array<string>
     */
    public function getWhereList(): array
    {
        return array_unique($this->whereList);
    }

    /**
     * @param array<string> $whereList
     */
    public function setWhereList(array $whereList): void
    {
        $this->whereList = $whereList;
    }

    public function addToWhereList(string $where): void
    {
        $this->whereList[] = $where;
    }

    /**
     * @return array<string>
     */
    public function getGroupList(): array
    {
        return array_unique($this->groupList);
    }

    /**
     * @param array<string> $groupList
     */
    public function setGroupList(array $groupList): void
    {
        $this->groupList = $groupList;
    }

    public function addToGroupList(string $group): void
    {
        $this->groupList[] = $group;
    }

    public function addToGroupExclusionList(string $group): void
    {
        $this->groupExclusionList[] = $group;
    }

    /**
     * @return array<string>
     */
    public function getGroupExclusionList(): array
    {
        return array_unique($this->groupExclusionList);
    }

    /**
     * @return array<string>
     */
    public function getSortList(): array
    {
        return array_unique($this->sortList);
    }

    /**
     * @param array<string> $sortList
     */
    public function setSortList(array $sortList): void
    {
        $this->sortList = $sortList;
    }

    /**
     * @return array<string>
     */
    public function getHavingList(): array
    {
        return array_unique($this->havingList);
    }

    /**
     * @param array<string> $havingList
     */
    public function setHavingList(array $havingList): void
    {
        $this->havingList = $havingList;
    }

    public function addToHavingList(string $having): void
    {
        $this->havingList[] = $having;
    }

    /**
     * @return array<string, int|string|bool|array>
     */
    public function getParameterList(): array
    {
        return $this->parameterList;
    }

    /**
     * @param array<string, int|string|bool|array> $parameterList
     */
    public function setParameterList(array $parameterList): void
    {
        $this->parameterList = $parameterList;
    }

    /**
     * @param array<string, int|string|bool|array> $parameters
     */
    public function addToParameterList(array $parameters): void
    {
        $this->parameterList = array_merge($this->parameterList, $parameters);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function buildQuery(): string
    {
        if ($this->from === '' || $this->getSelectList() === []) {
            return 'SELECT NULL';
        }

        $selectString = '';
        $joinString = '';
        $whereString = '';
        $groupString = '';
        $sortString = '';
        $havingString = '';
        $paginationString = '';

        if ($this->getSelectList() !== []) {
            $selectString = implode(', ', $this->getSelectList());
        }

        if ($this->getJoinList() !== []) {
            $joinString = implode(' ', $this->getJoinList());
        }

        if ($this->getWhereList() !== []) {
            $whereString = 'WHERE (' . implode(') AND (', $this->getWhereList()) . ')';
        }

        if ($this->getGroupList() !== []) {
            $this->groupListSafety();

            $groupString = 'GROUP BY ' . implode(', ', $this->getGroupList());
        }

        if ($this->getHavingList() !== []) {
            $havingString = 'HAVING (' . implode(') AND (', $this->getHavingList()) . ')';
        }

        if ($this->getSortList() !== []) {
            $sortString = 'ORDER BY ' . implode(', ', $this->getSortList());
        }

        if ($this->getLimit() > 0 && $this->getOffset() >= 0) {
            $paginationString = sprintf("LIMIT %d OFFSET %d", $this->getLimit(), $this->getOffset());
        }

        return sprintf(
            "SELECT %s FROM %s %s %s %s %s %s %s",
            $selectString,
            $this->getFrom(),
            $joinString,
            $whereString,
            $groupString,
            $havingString,
            $sortString,
            $paginationString,
        );
    }

    public function isFilterFieldNotEmpty(?RequestFilterField $filterField): bool
    {
        return $filterField !== null &&
            (
                $filterField->getValue() !== ''
                || $filterField->getValues() !== []
                || $filterField->getGreatOrEqual() !== ''
                || $filterField->getGreat() !== ''
                || $filterField->getLessOrEqual() !== ''
                || $filterField->getLess() !== ''
                || $filterField->getEqual() !== ''
                || $filterField->getNotEqual() !== ''
            );
    }

    public function prepareCondition(
        string $columnName,
        RequestFilterField $filterField,
        bool $isLike = false
    ): AssignCondition {
        $condition = new AssignCondition();
        $baseName = uniqid($filterField->getId());

        if ($filterField->getValue() !== '') {
            $placeholder = $baseName . 'Value';
            $like = $isLike ? 'LIKE ' : '=';
            $valueMask = $isLike ? '%%%s%%' : '%s';
            $condition->addAssignExpression(sprintf('%s %s :%s', $columnName, $like, $placeholder));
            $condition->addPlaceholder($placeholder, sprintf($valueMask, $filterField->getValue()));
        }

        if ($filterField->getValues() !== []) {
            $placeholder = $baseName . 'Values';
            $condition->addAssignExpression(sprintf('%s IN (:%s)', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getValues());
        }

        $placeholder = $baseName . 'MinValue';

        if ($filterField->getGreat() !== '') {
            $condition->addAssignExpression(sprintf('%s > :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getGreat());
        } elseif ($filterField->getGreatOrEqual() !== '') {
            $condition->addAssignExpression(sprintf('%s >= :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getGreatOrEqual());
        }

        $placeholder = $baseName . 'MaxValue';

        if ($filterField->getLess() !== '') {
            $condition->addAssignExpression(sprintf('%s < :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getLess());
        } elseif ($filterField->getLessOrEqual() !== '') {
            $condition->addAssignExpression(sprintf('%s <= :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getLessOrEqual());
        }

        $placeholder = $baseName . 'Equal';

        if ($filterField->getEqual() !== '') {
            $condition->addAssignExpression(sprintf('%s = :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getEqual());
        } elseif ($filterField->getNotEqual() !== '') {
            $condition->addAssignExpression(sprintf('%s != :%s', $columnName, $placeholder));
            $condition->addPlaceholder($placeholder, $filterField->getNotEqual());
        }

        return $condition;
    }

    /**
     * @return array<string>
     */
    public function createSortList(ReportRequest $reportRequest): array
    {
        $sortList = [];

        foreach ($reportRequest->getRequestDto()->getSort() as $columnName) {
            $operator = $columnName[0] === '-' ? 'DESC' : 'ASC';
            $sortList[] = sprintf('%s %s', ltrim($columnName, '-'), $operator);
        }

        return $sortList;
    }

    private function groupListSafety(): void
    {
        if ($this->getGroupList() === []) {
            return;
        }

        foreach ($this->getSelectList() as $selectString) {
            [$beforeAs, $afterAs] = array_pad(
                explode(' AS ', str_replace(' as ', ' AS ', $selectString)),
                2,
                null,
            );

            if (
                in_array($beforeAs, $this->getGroupList(), true)
                || in_array($afterAs, $this->getGroupList(), true)
                || $afterAs === null
            ) {
                continue;
            }

            $this->addToGroupList($afterAs);
        }

        foreach ($this->groupList as $index => $group) {
            if (in_array($group, $this->getGroupExclusionList(), true)) {
                unset($this->groupList[$index]);
            }
        }
    }
}
