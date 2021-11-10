<?php

declare(strict_types=1);

namespace Clickhouse\Migration;

use App\Clickhouse\Engine\EngineMySql;
use App\Clickhouse\Migration\AbstractClickhouseMigration;
use App\Clickhouse\Migration\ClickhouseMigrationInterface;
use App\Clickhouse\QueryBuilder;

final class Version000002 extends AbstractClickhouseMigration implements ClickhouseMigrationInterface
{

    public function getMigrationParams(): array
    {
        return [
            'CLICKHOUSE_MIGRATION_CORE_DB_HOST' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_NAME' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_PORT' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_USER' => null,
            'CLICKHOUSE_MIGRATION_CORE_DB_PASS' => null,
        ];
    }

    public function up(): bool
    {
        $config = $this->getMigrationConfig();

        $engine = new EngineMySql();
        $engine->setHost($config['CLICKHOUSE_MIGRATION_CORE_DB_HOST']);
        $engine->setPort($config['CLICKHOUSE_MIGRATION_CORE_DB_PORT']);
        $engine->setDatabase($config['CLICKHOUSE_MIGRATION_CORE_DB_NAME']);
        $engine->setTable('player');
        $engine->setUser($config['CLICKHOUSE_MIGRATION_CORE_DB_USER']);
        $engine->setPassword($config['CLICKHOUSE_MIGRATION_CORE_DB_PASS']);

        $this->createQueryBuilder()
            ->createTable('player', QueryBuilder::IF_NOT_EXISTS)
            ->addInt32('id')
            ->addUuid('external_id')
            ->addInt32('hall_id')
            ->addString('email')
            ->addString('phone')
            ->addString('status_cd')
            ->addString('first_name')
            ->addString('middle_name')
            ->addString('last_name')
            ->addString('gender_cd')
            ->addString('permanent_address')
            ->addString('city')
            ->addString('country_cd')
            ->addString('postcode')
            ->addInt32('created_at')
            ->addInt32('updated_at')
            ->addInt32('deleted_at')
            ->addString('account_id')
            ->addInt32('profile_completion')
            ->addInt32('player_points')
            ->addString('region')
            ->addInt32('banned_at')
            ->addInt32('banned_till')
            ->addString('banned_by')
            ->addString('ban_type_cd')
            ->addInt32('birthday')
            ->setEngine($engine)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'player',
            ) !== ''
        ) {
            $this->consoleOut('CORE table `player` linked successfully.');
        } else {
            $this->consoleOut('Linking CORE Table `player` failed');

            return false;
        }

        return true;
    }

    public function down(): bool
    {
        $tablesToDrop = [
            'player',
        ];

        foreach ($tablesToDrop as $table) {
            $qb = $this->createQueryBuilder();
            $qb->dropTable($table, QueryBuilder::IF_EXISTS)->getResult();
        }

        return true;
    }
}
