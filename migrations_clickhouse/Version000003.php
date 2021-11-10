<?php

declare(strict_types=1);

namespace Clickhouse\Migration;

use App\Clickhouse\DictionarySource\DictionarySourceMySql;
use App\Clickhouse\Migration\AbstractClickhouseMigration;
use App\Clickhouse\Migration\ClickhouseMigrationInterface;
use App\Clickhouse\QueryBuilder;

final class Version000003 extends AbstractClickhouseMigration implements ClickhouseMigrationInterface
{

    public function getMigrationParams(): array
    {
        return [
            'CLICKHOUSE_MIGRATION_CORE_DB_HOST' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_NAME' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_PORT' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_USER' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_PASS' => null,
            'CLICKHOUSE_MIGRATION_PLATFORM_DB_HOST' => null,
            'CLICKHOUSE_MIGRATION_PLATFORM_DB_NAME' => null,
            'CLICKHOUSE_MIGRATION_PLATFORM_DB_PORT' => null,
            'CLICKHOUSE_MIGRATION_PLATFORM_DB_USER' => null,
            'CLICKHOUSE_MIGRATION_PLATFORM_DB_PASS' => null,
        ];
    }

    public function up(): bool
    {
        $config = $this->getMigrationConfig();
        $qb = $this->createQueryBuilder();
        $dictionarySource = new DictionarySourceMySql(
            'hall',
            [$config['CLICKHOUSE_MIGRATION_CORE_DB_HOST']],
            $config['CLICKHOUSE_MIGRATION_CORE_DB_NAME'],
            (int) $config['CLICKHOUSE_MIGRATION_CORE_DB_PORT'],
            $config['CLICKHOUSE_MIGRATION_CORE_DB_USER'],
            $config['CLICKHOUSE_MIGRATION_CORE_DB_PASS'],
        );
        $qb->createDictionary('hall')
            ->addInt64('id')
            ->addUuid('external_id')
            ->addUInt('parent_id', QueryBuilder::HIERARCHICAL, 64)
            ->addString('name')
            ->setPrimaryKey('id')
            ->setDictionarySource($dictionarySource)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'hall',
            ) !== ''
        ) {
            $this->consoleOut('CORE table `hall` was linked to external dictionaries successfully.');
        } else {
            $this->consoleOut('Cannot add external dictionary `hall` to dictionaries.');

            return false;
        }

        $qb = $this->createQueryBuilder();
        $dictionarySource = new DictionarySourceMySql(
            'game_provider',
            [$config['CLICKHOUSE_MIGRATION_PLATFORM_DB_HOST']],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_NAME'],
            (int) $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_PORT'],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_USER'],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_PASS'],
        );
        $qb->createDictionary('game_provider')
            ->addInt64('id')
            ->addUuid('guid')
            ->addString('name')
            ->setPrimaryKey('id')
            ->setDictionarySource($dictionarySource)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'game_provider',
            ) !== ''
        ) {
            $this->consoleOut('CORE table `game_provider` was linked to external dictionaries successfully.');
        } else {
            $this->consoleOut('Cannot add external dictionary `game_provider` to dictionaries.');

            return false;
        }

        $qb = $this->createQueryBuilder();
        $dictionarySource = new DictionarySourceMySql(
            'game',
            [$config['CLICKHOUSE_MIGRATION_PLATFORM_DB_HOST']],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_NAME'],
            (int) $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_PORT'],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_USER'],
            $config['CLICKHOUSE_MIGRATION_PLATFORM_DB_PASS'],
        );
        $qb->createDictionary('game')
            ->addInt64('id')
            ->addUuid('guid')
            ->addString('original_name')
            ->setPrimaryKey('id')
            ->setDictionarySource($dictionarySource)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'game',
            ) !== ''
        ) {
            $this->consoleOut('CORE table `game` was linked to external dictionaries successfully.');
        } else {
            $this->consoleOut('Cannot add external dictionary `game` to dictionaries.');

            return false;
        }

        return true;
    }

    public function down(): bool
    {
        $dictionaryToDrop = [
            'hall',
            'game_provider',
            'game',
        ];

        foreach ($dictionaryToDrop as $dictionaryName) {
            $qb = $this->createQueryBuilder();
            $qb->dropDictionary($dictionaryName)->getResult();
        }

        return true;
    }
}
