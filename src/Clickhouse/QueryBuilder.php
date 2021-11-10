<?php

declare(strict_types=1);

namespace App\Clickhouse;

use App\Clickhouse\DictionarySource\DictionarySourceInterface;
use App\Clickhouse\DictionarySource\Exception\DictionarySourceException;
use App\Clickhouse\Engine\EngineInterface;
use ClickHouseDB\Client;
use ClickHouseDB\Statement;
use InvalidArgumentException;

class QueryBuilder
{

    public const DECIMAL_PRECISION_DEFAULT = 18;

    // Bitmask flags

    public const IS_NULLABLE = 1;

    public const IF_NOT_EXISTS = 2;

    public const IF_EXISTS = 4;

    public const DEFAULT_ZERO = 8;

    public const HIERARCHICAL = 16;

    // Type's templates

    private const TYPE_DATE_TIME = 'DateTime%s';

    private const TYPE_UUID = 'UUID';

    private const TYPE_STRING = 'String';

    private const TYPE_DECIMAL = 'Decimal(%d, %d)';

    private const TYPE_ENUM = 'Enum%d(%s)';

    private const TYPE_UINT = 'UInt%d';

    private const TYPE_INT = 'Int%d';

    private const TYPE_NULLABLE = 'Nullable(%s)';

    private const TYPE_DEFAULT_ZERO = '%s default 0';

    private const TYPE_HIERARCHICAL = 'HIERARCHICAL';

    // Query types
    private const QUERY_TYPE_CREATE_TABLE = 1;

    private const QUERY_TYPE_CREATE_MATERIALIZED_VIEW = 2;

    private const QUERY_TYPE_CREATE_DICTIONARY = 3;

    private const QUERY_TYPE_SELECT = 4;

    private const QUERY_TYPE_DROP_TABLE = 5;

    private const QUERY_TYPE_DROP_VIEW = 6;

    private const QUERY_TYPE_INSERT_RECORD = 7;

    private const QUERY_TYPE_DELETE = 8;

    private const QUERY_TYPE_DROP_DICTIONARY = 9;

    private int $queryType = self::QUERY_TYPE_SELECT;

    /**
     * @var array <string, string|int|bool|null>
     */
    private array $columns = [];

    /**
     * @var array <string, string|int|bool|null>
     */
    private array $selectedColumns = [];

    private string $fromTableName = '';

    private string $tableName = '';

    private string $toTableName = '';

    private string $where = '';

    private ?int $limit = null;

    private ?int $offset = null;

    private QueryBuilder $nested;

    private int $flags = 0;

    private string $orderBy = '';

    private string $primaryKey;

    private ?DictionarySourceInterface $dictionarySource = null;

    private ?EngineInterface $engine = null;

    /**
     * @var array <string, string|int|bool|null>
     */
    private array $bindings = [];

    /**
     * @var array <string, string|int|bool|null>
     */
    private array $insertData = [];

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function addDateTime(string $columnName, int $flags = 0, int $size = 64): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getDateTimeType($size),
            );

        return $this;
    }

    /**
     * @param array<string,int> $values
     */
    public function addEnum(string $columnName, array $values, int $flags = 0, int $size = 8): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getEnum($values, $size),
            );

        return $this;
    }

    public function addInt(string $columnName, int $flags = 0, int $size = 64): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getInt($size),
            );

        return $this;
    }

    public function addInt8(string $columnName, int $flags = 0): QueryBuilder
    {
        return $this->addInt($columnName, $flags, 8);
    }

    public function addInt16(string $columnName, int $flags = 0): QueryBuilder
    {
        return $this->addInt($columnName, $flags, 16);
    }

    public function addInt32(string $columnName, int $flags = 0): QueryBuilder
    {
        return $this->addInt($columnName, $flags, 32);
    }

    public function addInt64(string $columnName, int $flags = 0): QueryBuilder
    {
        return $this->addInt($columnName, $flags, 64);
    }

    public function addUInt(string $columnName, int $flags = 0, int $size = 8): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getUInt($size),
            );

        return $this;
    }

    public function addUuid(string $columnName, int $flags = 0): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getUuidType(),
            );

        return $this;
    }

    public function addString(string $columnName, int $flags = 0): QueryBuilder
    {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getStringType(),
            );

        return $this;
    }

    public function addDecimal(
        string $columnName,
        int $flags = 0,
        int $precision = 38,
        int $scale = 18
    ): QueryBuilder {
        $this->columns[$columnName] =
            $this->applyFlags(
                $flags,
                $this->getDecimalType($precision, $scale),
            );

        return $this;
    }

    /**
     * @param array<string, string|int|bool|null> $columns
     */
    public function selectColumns(array $columns): QueryBuilder
    {
        $this->selectedColumns = $columns;

        return $this;
    }

    public function selectAllColumns(): QueryBuilder
    {
        $this->selectedColumns = ['*'];

        return $this;
    }

    public function setOrderBy(string $orderBy): QueryBuilder
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function setTableName(string $tableName): QueryBuilder
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function setToTableName(string $toTableName): QueryBuilder
    {
        $this->toTableName = $toTableName;

        return $this;
    }

    public function setEngine(EngineInterface $engine): QueryBuilder
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * @param array<string, string|int|bool|null> $bindings
     */
    public function bindings(array $bindings): QueryBuilder
    {
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * @param array<string, string|int|bool|null> $insertData
     */
    public function insert(string $tableName, array $insertData, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_INSERT_RECORD;
        $this->setTableName($tableName);
        $this->flags = $flags;
        $this->insertData = $insertData;

        return $this;
    }

    public function delete(string $tableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_DELETE;
        $this->setTableName($tableName);
        $this->flags = $flags;

        return $this;
    }

    public function createTable(string $tableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_CREATE_TABLE;
        $this->setTableName($tableName);
        $this->flags = $flags;

        return $this;
    }

    public function createDictionary(string $tableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_CREATE_DICTIONARY;
        $this->setTableName($tableName);
        $this->flags = $flags;

        return $this;
    }

    public function createMaterializedView(string $tableName, string $toTableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_CREATE_MATERIALIZED_VIEW;
        $this->setTableName($tableName);
        $this->setToTableName($toTableName);
        $this->flags = $flags;

        return $this;
    }

    public function from(string $fromTableName): QueryBuilder
    {
        $this->fromTableName = $fromTableName;

        return $this;
    }

    public function where(string $where): QueryBuilder
    {
        $this->where = $where;

        return $this;
    }

    public function limit(int $limit): QueryBuilder
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): QueryBuilder
    {
        $this->offset = $offset;

        return $this;
    }

    public function dropTable(string $tableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_DROP_TABLE;
        $this->setTableName($tableName);
        $this->flags = $flags;

        return $this;
    }

    public function dropView(string $tableName, int $flags = 0): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_DROP_VIEW;
        $this->setTableName($tableName);
        $this->flags = $flags;

        return $this;
    }

    public function getQuery(): ?string
    {
        // @codingStandardsIgnoreStart
        switch ($this->queryType) {
            case self::QUERY_TYPE_CREATE_TABLE:

                return $this->renderCreateTableQuery();
            case self::QUERY_TYPE_CREATE_DICTIONARY:

                return $this->renderCreateDictionaryQuery();
            case self::QUERY_TYPE_CREATE_MATERIALIZED_VIEW:

                return $this->renderCreateMaterializedViewQuery();
            case self::QUERY_TYPE_DROP_TABLE:

                return $this->renderDropTable();
            case self::QUERY_TYPE_DROP_VIEW:

                return $this->renderDropView();
            case self::QUERY_TYPE_DELETE:

                return $this->renderDeleteQuery();
            case self::QUERY_TYPE_DROP_DICTIONARY:

                return $this->renderDropDictionaryQuery();
            default:

                return $this->renderSelectQuery();
        }
        // @codingStandardsIgnoreEnd
    }

    public function getResult(): Statement
    {
        $statement = null;

        // @codingStandardsIgnoreStart
        switch ($this->queryType) {
            case self::QUERY_TYPE_CREATE_MATERIALIZED_VIEW:
            case self::QUERY_TYPE_CREATE_TABLE:
            case self::QUERY_TYPE_CREATE_DICTIONARY:
            case self::QUERY_TYPE_DROP_VIEW:
            case self::QUERY_TYPE_DROP_TABLE:
            case self::QUERY_TYPE_DROP_DICTIONARY:
            case self::QUERY_TYPE_DELETE:
                $sql = $this->getQuery();
                $statement = $this->client->write($sql);
                break;
            case self::QUERY_TYPE_INSERT_RECORD:
                $statement = $this->client->insert(
                    $this->tableName,
                    [
                        array_values($this->insertData)
                    ],
                    array_keys($this->insertData),
                );
                break;
            default:
                $sql = $this->getQuery();
                $statement = $this->client->select($sql, $this->bindings);
        }
        // @codingStandardsIgnoreEnd

        return $statement;
    }

    public function JSONExtractString(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractString(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function JSONExtractBool(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractBool(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function JSONExtractFloat(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractFloat(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function JSONExtractUInt(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractUInt(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function JSONExtractInt(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractInt(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function JSONExtractRaw(string $jsonSource, string $jsonFieldName): string
    {
        return sprintf("JSONExtractRaw(%s, '%s')", $jsonSource, $jsonFieldName);
    }

    public function fromUnixTime(string $source): string
    {
        return sprintf('FROM_UNIXTIME(%s)', $source);
    }

    public function toDecimal128(
        string $source,
        int $precision = self::DECIMAL_PRECISION_DEFAULT,
        bool $isOrNull = true
    ): string {
        return sprintf(
            'toDecimal128%s(%s,%d)',
            $isOrNull ? 'OrNull' : '',
            $source,
            $precision,
        );
    }

    public function toUInt64(string $source, bool $isOrNull = true): string
    {
        return sprintf(
            'toUInt64%s(%s)',
            $isOrNull ? 'OrNull' : '',
            $source,
        );
    }

    public function ternaryExpression(string $ifExpression, string $thenExpression, string $elseExpression): string
    {
        return sprintf('%s ? %s : %s', $ifExpression, $thenExpression, $elseExpression);
    }

    /**
     * @param array<int, string> $keyChain
     */
    public function JSONExtractUInt64Safe(string $source, array $keyChain): string
    {
        if ($keyChain === []) {
            throw new InvalidArgumentException('$keyChain must be not empty array<string>.');
        }

        return $this->ternaryExpression(
            $this->isNull($this->toUInt64($this->JSONExtractRawByPath($source, $keyChain))),
            $this->toUInt64($this->JSONExtractStringByPath($source, $keyChain)),
            $this->toUInt64($this->JSONExtractRawByPath($source, $keyChain)),
        );
    }

    /**
     * @param array<int, string> $keyChain
     */
    public function JSONExtractDecimal128Safe(string $source, array $keyChain): string
    {
        if ($keyChain === []) {
            throw new InvalidArgumentException('$keyChain must be not empty array<string>.');
        }

        return $this->ternaryExpression(
            $this->isNull($this->toDecimal128($this->JSONExtractRawByPath($source, $keyChain))),
            $this->toDecimal128($this->JSONExtractStringByPath($source, $keyChain)),
            $this->toDecimal128($this->JSONExtractRawByPath($source, $keyChain)),
        );
    }

    public function isNull(string $expression): string
    {
        return sprintf('(%s) IS NULL', $expression);
    }

    /**
     * @param array<string> $list
     */
    public function isIn(string $expression, array $list): string
    {
        return sprintf("%s IN ('%s')", $expression, implode("','", $list));
    }

    /**
     * @param array<int, string> $keyChain
     */
    public function JSONExtractRawByPath(string $source, array $keyChain): string
    {
        if ($keyChain === []) {
            throw new InvalidArgumentException('$keyChain must be not empty array<string>.');
        }

        $result = $this->JSONExtractRaw($source, array_shift($keyChain));

        foreach ($keyChain as $key) {
            $result = $this->JSONExtractRaw($result, $key);
        }

        return $result;
    }

    /**
     * @param array<int, string> $keyChain
     */
    public function JSONExtractStringByPath(string $source, array $keyChain): string
    {
        if ($keyChain === []) {
            throw new InvalidArgumentException('$keyChain must be not empty array<string>.');
        }

        $result = $this->JSONExtractRaw($source, array_shift($keyChain));

        foreach ($keyChain as $index => $key) {
            $result = $index + 1 < count($keyChain)
                ? $this->JSONExtractRaw($result, $key)
                : $this->JSONExtractString($result, $key);
        }

        return $result;
    }

    public function toUuid(string $source, bool $isOrNull = true): string
    {
        return sprintf(
            'toUUID%s(%s)',
            $isOrNull ? 'OrNull' : '',
            $source,
        );
    }

    public function setPrimaryKey(string $pkColumn): QueryBuilder
    {
        $this->primaryKey = $pkColumn;

        return $this;
    }

    public function setDictionarySource(DictionarySourceInterface $dictionarySource): QueryBuilder
    {
        $this->dictionarySource = $dictionarySource;

        return $this;
    }

    public function getDictionarySource(): ?DictionarySourceInterface
    {
        return $this->dictionarySource;
    }

    public function dropDictionary(string $dictionaryName): QueryBuilder
    {
        $this->queryType = self::QUERY_TYPE_DROP_DICTIONARY;
        $this->tableName = $dictionaryName;

        return $this;
    }

    private function getInt(int $size): string
    {
        return sprintf(self::TYPE_INT, $size);
    }

    private function getDateTimeType(int $size): string
    {
        return sprintf(self::TYPE_DATE_TIME, $size);
    }

    private function getUuidType(): string
    {
        return self::TYPE_UUID;
    }

    private function getStringType(): string
    {
        return self::TYPE_STRING;
    }

    private function getDecimalType(int $precision, int $scale): string
    {
        return sprintf(self::TYPE_DECIMAL, $precision, $scale);
    }

    private function getUInt(int $size): string
    {
        return sprintf(self::TYPE_UINT, $size);
    }

    /**
     * @param array<string, int> $values
     */
    private function getEnum(array $values, int $size = 8): string
    {
        $enumValues = [];

        foreach ($values as $alias => $number) {
            $enumValues[] = sprintf("'%s' = %d", $alias, $number);
        }

        return sprintf(self::TYPE_ENUM, $size, implode(',', $enumValues));
    }

    private function applyFlags(int $flags, string $columnType): string
    {
        $outputType = $columnType;

        if ($flags & self::DEFAULT_ZERO) {
            $outputType = sprintf(self::TYPE_DEFAULT_ZERO, $outputType);
        }

        if ($flags & self::IS_NULLABLE) {
            $outputType = sprintf(self::TYPE_NULLABLE, $outputType);
        }

        if ($flags & self::HIERARCHICAL) {
            $outputType .= ' ' . self::TYPE_HIERARCHICAL;
        }

        return $outputType;
    }

    private function renderDropTable(): string
    {
        $ifExists = ($this->flags & self::IF_EXISTS) ? 'IF EXISTS' : '';

        return sprintf('DROP TABLE %s %s', $ifExists, $this->tableName);
    }

    private function renderDeleteQuery(): string
    {
        $sql = sprintf('ALTER TABLE %s DELETE', $this->tableName);

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }

        return $sql;
    }

    private function renderDropView(): string
    {
        $ifExists = ($this->flags & self::IF_EXISTS) ? 'IF EXISTS' : '';

        return sprintf('DROP VIEW %s %s', $ifExists, $this->tableName);
    }

    /**
     * @throws DictionarySourceException
     */
    private function renderCreateDictionaryQuery(): string
    {
        if ($this->dictionarySource === null) {
            throw new DictionarySourceException(DictionarySourceException::DICTIONARY_SOURCE_EMPTY);
        }

        $columns = [];

        foreach ($this->columns as $columnName => $columnType) {
            $columns[] = sprintf('%s %s', $columnName, $columnType);
        }

        return sprintf(
            ' CREATE DICTIONARY %s '
            . ' (%s) '
            . 'PRIMARY KEY %s '
            . 'SOURCE(%s) '
            . 'LAYOUT(%s) '
            . 'LIFETIME(%s) ',
            $this->tableName,
            implode(', ', $columns),
            $this->primaryKey,
            $this->getDictionarySource()->getSourceConnectionString(),
            $this->getDictionarySource()->getLayout(),
            $this->getDictionarySource()->getLifetime(),
        );
    }

    private function renderCreateTableQuery(): string
    {
        $columns = [];

        foreach ($this->columns as $columnName => $columnType) {
            $columns[] = sprintf('%s %s', $columnName, $columnType);
        }

        $ifNotExists = ($this->flags & self::IF_NOT_EXISTS) ? 'IF NOT EXISTS' : '';

        $sql = sprintf(
            ' CREATE TABLE'
            . ' %s '
            . ' %s '
            . ' (%s) '
            . ' ENGINE = %s ',
            $ifNotExists,
            $this->tableName,
            implode(', ', $columns),
            $this->engine->getConnectionDsnString(),
        );

        if (
            $this->engine->canUseConnectionSettings()
            && $this->engine->getConnectionSettingsString() !== ''
        ) {
            $sql .= " SETTINGS " . $this->engine->getConnectionSettingsString();
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . $this->orderBy;
        }

        return $sql;
    }

    private function renderSelectQuery(): string
    {
        $columns = [];

        foreach ($this->selectedColumns as $columnName => $columnSource) {
            if ($columnSource === '*') {
                $columns[] = '*';
            } else {
                $columns[] = sprintf("%s as %s", $columnSource, $columnName);
            }
        }

        $sql = sprintf(
            ' SELECT '
            . ' %s '
            . ' FROM '
            . ' %s',
            implode(', ', $columns),
            $this->fromTableName,
        );

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->where;
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . $this->orderBy;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . (string)$this->offset;
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . (string)$this->limit;
        }

        return $sql;
    }

    private function renderCreateMaterializedViewQuery(): string
    {
        $ifNotExists = ($this->flags & self::IF_NOT_EXISTS) ? 'IF NOT EXISTS' : '';

        $sql =
            sprintf(
                ' CREATE MATERIALIZED VIEW '
                . ' %s '
                . ' %s '
                . ' TO '
                . ' %s '
                . ' AS  '
                . ' %s; ',
                $ifNotExists,
                $this->tableName,
                $this->toTableName,
                $this->renderSelectQuery(),
            );

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . $this->orderBy;
        }

        return $sql;
    }

    private function renderDropDictionaryQuery(): string
    {
        return sprintf('DROP DICTIONARY %s', $this->tableName);
    }
}
