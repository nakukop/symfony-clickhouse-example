<?php

declare(strict_types=1);

namespace App\Clickhouse\Migration;

use App\Clickhouse\Engine\EngineMergeTree;
use App\Clickhouse\QueryBuilder;
use App\DB\Connection;
use ClickHouseDB\Client;
use Exception;
use Throwable;

class ClickhouseMigrationRunner
{

    public const SERVICE_TAG = 'clickhouse.migration.runner';

    /**
     * @var array<ClickhouseMigrationInterface>
     */
    private array $migrations = [];
    
    private Client $client;

    private ?MigrationOutputInterface $output = null;

    public function __construct(Connection $connection)
    {
        $this->client = $connection->getConnection();
    }
    
    /**
     * @return array<ClickhouseMigrationInterface>
     */
    public function getMigrations(): array
    {
        ksort($this->migrations);

        return $this->migrations;
    }

    public function addMigration(ClickhouseMigrationInterface $migration): void
    {
        $this->migrations[$migration->getVersion()] = $migration;
    }

    public function setOutput(MigrationOutputInterface $output): void
    {
        $this->output = $output;

        foreach ($this->getMigrations() as $migration) {
            $migration->setOutput($output);
        }
    }

    public function runUp(): void
    {
        if (!$this->isReadyMigrationsTable()) {
            throw new Exception('Can not create `migrations` table. Exit 1');
        }

        try {
            foreach ($this->getMigrations() as $migration) {
                assert($migration instanceof ClickhouseMigrationInterface);

                if ($this->canUp($migration->getVersion()) && $migration->up()) {
                    $this->addToMigrationsTable($migration);
                }

                $this->consoleOut('Migrations is upped to version: ' . $migration->getVersion());
            }
        } catch (Throwable $exception) {
            throw new Exception('Migration procedure is corrupted: ' . $exception->getMessage());
        }
    }

    public function runDown(?string $versionToRollback): void
    {
        if (!$this->isReadyMigrationsTable()) {
            throw new Exception('Can not create `migrations` table. Exit 1');
        }

        if ($versionToRollback === null) {
            $versionToRollback = $this->getLastVersion();
        }

        try {
            if ($versionToRollback !== null && $this->canRollbackToVersion($versionToRollback)) {
                foreach ($this->getMigrations() as $migration) {
                    if (
                        $migration->getVersion() >= $versionToRollback
                        && $this->canDown($migration->getVersion())
                        && $migration->down()
                    ) {
                        $this->removeFromMigrationsTable($migration);
                    }
                }

                $this->consoleOut('Rollbacked to version: ' . $versionToRollback);

                return;
            } else {
                throw new Exception('Cannot rollback to version: ' . $versionToRollback);
            }
        } catch (Throwable $exception) {
            throw new Exception('Migration failed: ' . $exception->getMessage());
        }
    }

    protected function consoleOut(string $message): void
    {
        if ($this->output !== null) {
            $this->output->writeln($message);
        }
    }

    private function addToMigrationsTable(ClickhouseMigrationInterface $migration): void
    {
        $qb = $this->createQueryBuilder();
        $qb->insert(
            'migrations',
            [
                'uuid' => uuid_create(),
                'timestamp' => time(),
                'version' => $migration->getVersion(),
            ],
        )->getResult();
    }

    private function removeFromMigrationsTable(ClickhouseMigrationInterface $migration): void
    {
        $qb = $this->createQueryBuilder();
        $qb->delete('migrations')
            ->where(sprintf("version='%s'", $migration->getVersion()))
            ->getResult();
    }

    private function isReadyMigrationsTable(): bool
    {
        if (array_key_exists('migrations', $this->client->showTables())) {
            return true;
        }

        $qb = $this->createQueryBuilder();
        $qb->createTable('migrations', QueryBuilder::IF_NOT_EXISTS)
            ->addUuid('uuid')
            ->addInt('timestamp')
            ->addString('version')
            ->setEngine(new EngineMergeTree())
            ->setOrderBy('timestamp')
            ->getResult();

        return (string)$this->client->showCreateTable('migrations') !== '';
    }

    private function canUp(string $migrationVersion): bool
    {
        $qb = $this->createQueryBuilder();
        $qb->selectAllColumns()
            ->from('migrations')
            ->where('version=:version')
            ->limit(1)
            ->bindings(['version' => $migrationVersion]);
        $statement = $qb->getResult();

        return $statement->count() === 0;
    }

    private function canDown(string $migrationVersion): bool
    {
        $qb = $this->createQueryBuilder();
        $qb->selectAllColumns()
            ->from('migrations')
            ->where('version=:version')
            ->limit(1)
            ->bindings(['version' => $migrationVersion]);
        $statement = $qb->getResult();

        return (bool)$statement->count();
    }

    private function canRollbackToVersion(string $migrationVersion): bool
    {
        if ($migrationVersion === '') {
            return false;
        }

        $isFindInDI = false;

        foreach ($this->migrations as $migration) {
            if ($migration->getVersion() === $migrationVersion) {
                $isFindInDI = true;

                break;
            }
        }

        $isFindInDB = $this->canDown($migrationVersion);

        return $isFindInDB && $isFindInDI;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->client);
    }

    private function getLastVersion(): ?string
    {
        $qb = $this->createQueryBuilder();
        $qb->selectColumns(['version' => 'version'])
            ->from('migrations')
            ->setOrderBy('timestamp DESC, version DESC')
            ->limit(1);

        return $qb->getResult()->fetchOne('version');
    }
}
