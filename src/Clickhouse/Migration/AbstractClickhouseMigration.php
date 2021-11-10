<?php

declare(strict_types=1);

namespace App\Clickhouse\Migration;

use App\Clickhouse\QueryBuilder;
use App\DB\Connection;
use ClickHouseDB\Client;
use ClickHouseDB\Statement;
use Exception;
use ReflectionClass;

abstract class AbstractClickhouseMigration implements ClickhouseMigrationInterface
{
    private Client $client;

    private ?MigrationOutputInterface $output = null;

    /**
     * @var array<string, string>
     */
    private array $migrationConfig;

    abstract public function up(): bool;

    abstract public function down(): bool;

    public function __construct(
        Connection $connection
    ) {
        $this->client = $connection->getConnection();
        $this->migrationConfig = [];
    }

    final public function getVersion(): string
    {
        $currentMigrationReflector = new ReflectionClass($this);

        return $currentMigrationReflector->getShortName();
    }

    /**
     * @return array<string, string>
     */
    public function getMigrationParams(): array
    {
        return [];
    }

    public function setOutput(MigrationOutputInterface $output): void
    {
        $this->output = $output;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->client);
    }

    final protected function runSQL(string $sql): Statement
    {
        return $this->client->write($sql);
    }

    final protected function getClient(): Client
    {
        return $this->client;
    }

    protected function consoleOut(string $message): void
    {
        if ($this->output !== null) {
            $this->output->writeln($message);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getMigrationConfig(): array
    {
        if (empty($this->migrationConfig)) {
            $migrationParams = $this->getMigrationParams();

            foreach ($migrationParams as $param => $default) {
                $value = getenv($param);

                if ($value === false && $default === null) {
                    throw new Exception('Required parameter `' . $param . '` is undefined in app configuration!');
                }

                if ($value === '' && $default === null) {
                    throw new Exception('Required parameter `' . $param . '` cannot be empty!');
                }

                $this->migrationConfig[$param] = ($value !== false) ? $value : $default;
            }
        }

        return $this->migrationConfig;
    }
}
