<?php

declare(strict_types=1);

namespace Clickhouse\Migration;

use App\Clickhouse\Engine\EngineKafka;
use App\Clickhouse\Engine\EngineMergeTree;
use App\Clickhouse\Migration\AbstractClickhouseMigration;
use App\Clickhouse\Migration\ClickhouseMigrationInterface;
use App\Clickhouse\QueryBuilder;

final class Version000001 extends AbstractClickhouseMigration implements ClickhouseMigrationInterface
{

    public function getMigrationParams(): array
    {
        return [
            //brokers and topics
            'CLICKHOUSE_MIGRATION_KAFKA_BROKER_LIST' => null,
            'CLICKHOUSE_MIGRATION_KAFKA_PAYMENT_TOPIC' => null,
            'CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_TRANSACTIONS_TOPIC' => null,
            'CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_SESSIONS_TOPIC' => null,
            'CLICKHOUSE_MIGRATION_KAFKA_BETTING_TOPIC' => null,

            //tables engines
            'CLICKHOUSE_MIGRATION_ENGINE_TABLE_PAYMENT' => EngineMergeTree::ENGINE_TYPE,
            'CLICKHOUSE_MIGRATION_ENGINE_TABLE_GAMBLING' => EngineMergeTree::ENGINE_TYPE,
            'CLICKHOUSE_MIGRATION_ENGINE_TABLE_BETTING' => EngineMergeTree::ENGINE_TYPE,
        ];
    }

    public function up(): bool
    {
        return (
                $this->createPaymentsTables()
                && $this->createGamblingTables()
                && $this->createBettingTables()
            ) === true;
    }

    public function down(): bool
    {
        $materializeViewsToDrop = [
            'betting_transactions_consumer',
            'gambling_transactions_consumer',
            'gambling_sessions_consumer',
            'payment_transactions_consumer',
        ];

        $tablesToDrop = [
            'payment_transactions_queue',
            'payment_transactions',
            'gambling_sessions_queue',
            'gambling_transactions_queue',
            'gambling_transactions',
            'gambling_sessions',
            'betting_transactions_queue',
            'betting_transactions',
        ];

        foreach ($materializeViewsToDrop as $materializedView) {
            $qb = $this->createQueryBuilder();
            $qb->dropView($materializedView, QueryBuilder::IF_EXISTS)->getResult();
        }

        foreach ($tablesToDrop as $table) {
            $qb = $this->createQueryBuilder();
            $qb->dropTable($table, QueryBuilder::IF_EXISTS)->getResult();
        }

        return true;
    }

    private function createPaymentsTables(): bool
    {
        $config = $this->getMigrationConfig();

        $qb = $this->createQueryBuilder();
        $engine = new EngineKafka();
        $engine->setConnectionSettings([
            'kafka_broker_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_BROKER_LIST'],
            'kafka_topic_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_PAYMENT_TOPIC'],
            'kafka_group_name' => sprintf('%s_clickhouse', $config['CLICKHOUSE_MIGRATION_KAFKA_PAYMENT_TOPIC']),
            'kafka_format' => 'JSONAsString',
            'kafka_num_consumers' => 1
        ]);

        $qb->createTable('payment_transactions_queue', QueryBuilder::IF_NOT_EXISTS)
            ->addString('message')
            ->setEngine($engine)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'payment_transactions_queue',
            ) !== ''
        ) {
            $this->consoleOut('Table `payment_transactions_queue` is created successfully.');
        } else {
            $this->consoleOut('Table `payment_transactions_queue` does not exist.');

            return false;
        }

        $engine = new EngineMergeTree($config['CLICKHOUSE_MIGRATION_ENGINE_TABLE_PAYMENT']);

        $qb = $this->createQueryBuilder();
        $qb->createTable('payment_transactions', QueryBuilder::IF_NOT_EXISTS)
            ->addDateTime('timestamp')
            ->addUuid('casino_id')
            ->addUuid('hall_id')
            ->addUuid('player_id')
            ->addString('currency_code')
            ->addDecimal('amount', QueryBuilder::IS_NULLABLE)
            ->addUuid('transaction_id')
            ->addEnum('transaction_type', ['deposit' => 1, 'withdrawal' => 2], QueryBuilder::IS_NULLABLE)
            ->addString('country_code', QueryBuilder::IS_NULLABLE)
            ->setEngine($engine)
            ->setOrderBy('timestamp')
            ->getResult();

        if ((string)$this->getClient()->showCreateTable('payment_transactions') !== '') {
            $this->consoleOut('Table `payment_transactions` is created successfully.');
        } else {
            $this->consoleOut('Table `payment_transactions` does not exist.');

            return false;
        }

        $qb = $this->createQueryBuilder();
        $qb->createMaterializedView(
            'payment_transactions_consumer',
            'payment_transactions',
            QueryBuilder::IF_NOT_EXISTS
        )
            ->selectColumns(
                [
                    'timestamp' => $qb->fromUnixTime($qb->toUInt64($qb->JSONExtractRaw('message', 'timestamp'))),
                    'casino_id' => $qb->JSONExtractString('message', 'casino_id'),
                    'hall_id' => $qb->JSONExtractString('message', 'hall_id'),
                    'player_id' => $qb->JSONExtractString('message', 'player_id'),
                    'currency_code' => $qb->JSONExtractString('message', 'currency_code'),
                    'amount' => $qb->toDecimal128($qb->JSONExtractString('message', 'amount')),
                    'transaction_id' => $qb->JSONExtractString('message', 'transaction_id'),
                    'transaction_type' => $qb->JSONExtractString('message', 'transaction_type'),
                    'country_code' => $qb->JSONExtractString('message', 'country_code')
                ]
            )
            ->from('payment_transactions_queue')
            ->getResult();

        if ((string)$this->getClient()->showCreateTable('payment_transactions_consumer') !== '') {
            $this->consoleOut('Table `payment_transactions_consumer` is created successfully.');
        } else {
            $this->consoleOut('Table `payment_transactions_consumer` does not exist.');

            return false;
        }

        return true;
    }

    private function createGamblingTables(): bool
    {
        $config = $this->getMigrationConfig();
        $qb = $this->createQueryBuilder();
        $engineSessions = new EngineKafka();
        $engineSessions->setConnectionSettings([
            'kafka_broker_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_BROKER_LIST'],
            'kafka_topic_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_SESSIONS_TOPIC'],
            'kafka_group_name' => sprintf(
                '%s_clickhouse',
                $config['CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_SESSIONS_TOPIC']
            ),
            'kafka_format' => 'JSONAsString',
            'kafka_num_consumers' => 1,
        ]);
        $qb->createTable('gambling_sessions_queue', QueryBuilder::IF_NOT_EXISTS)
            ->addString('message')
            ->setEngine($engineSessions)
            ->getResult();

        $engineTransactions = new EngineKafka();
        $engineTransactions->setConnectionSettings([
            'kafka_broker_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_BROKER_LIST'],
            'kafka_topic_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_TRANSACTIONS_TOPIC'],
            'kafka_group_name' => sprintf(
                '%s_clickhouse',
                $config['CLICKHOUSE_MIGRATION_KAFKA_GAMBLING_TRANSACTIONS_TOPIC']
            ),
            'kafka_format' => 'JSONAsString',
            'kafka_num_consumers' => 1
        ]);
        $qb->createTable('gambling_transactions_queue', QueryBuilder::IF_NOT_EXISTS)
            ->addString('message')
            ->setEngine($engineTransactions)
            ->getResult();


        foreach (['gambling_transactions_queue', 'gambling_sessions_queue'] as $queueTable) {
            if ((string)$this->getClient()->showCreateTable($queueTable) !== '') {
                $this->consoleOut('Table `' . $queueTable . '` is created successfully.');
            } else {
                $this->consoleOut('Table `' . $queueTable . '` does not exist.');

                return false;
            }
        }

        $deviceEnum = ['DEVICE_NULL' => 0, 'DEVICE_MOBILE' => 1, 'DEVICE_TABLET' => 2,
            'DEVICE_DESKTOP' => 3, 'DEVICE_OTHER' => 4];
        $gameModeEnum = ['GAME_MODE_REAL' => 0, 'GAME_MODE_DEMO' => 1];

        $engineMergeTree = new EngineMergeTree($config['CLICKHOUSE_MIGRATION_ENGINE_TABLE_GAMBLING']);
        $qb = $this->createQueryBuilder();
        $qb->createTable('gambling_sessions', QueryBuilder::IF_NOT_EXISTS)
            ->addUInt('id', QueryBuilder::IS_NULLABLE, 64)
            ->addUInt('hall_id', QueryBuilder::IS_NULLABLE, 64)
            ->addUuid('hall_external_id', QueryBuilder::IS_NULLABLE)
            ->addUuid('platform_id', QueryBuilder::IS_NULLABLE)
            ->addString('player_account_id', QueryBuilder::IS_NULLABLE)
            ->addUInt('player_id', QueryBuilder::IS_NULLABLE, 64)
            ->addUuid('player_external_id', QueryBuilder::IS_NULLABLE)
            ->addString('region', QueryBuilder::IS_NULLABLE)
            ->addString('country_code', QueryBuilder::IS_NULLABLE)
            ->addString('ip', QueryBuilder::IS_NULLABLE)
            ->addEnum('device', $deviceEnum, QueryBuilder::IS_NULLABLE)
            ->addString('source', QueryBuilder::IS_NULLABLE)
            ->addUInt('provider_external_id', QueryBuilder::IS_NULLABLE, 64)
            ->addString('product', QueryBuilder::IS_NULLABLE)
            ->addString('game_type_name', QueryBuilder::IS_NULLABLE)
            ->addEnum('game_mode', $gameModeEnum, QueryBuilder::IS_NULLABLE)
            ->addUInt('game_external_id', QueryBuilder::IS_NULLABLE, 64)
            ->addUuid('token', QueryBuilder::IS_NULLABLE)
            ->addString('currency_code', QueryBuilder::IS_NULLABLE)
            ->addUInt('started_at', 0, 64)
            ->setEngine($engineMergeTree)
            ->setOrderBy('started_at')
            ->getResult();

        $qb = $this->createQueryBuilder();
        $gamblingTransactionTypeEnum = ['TYPE_NULL' => 0, 'TYPE_BET' => 1, 'TYPE_WIN' => 2, 'TYPE_REFUND' => 3,
            'TYPE_ROLLBACK' => 4, 'TYPE_JACKPOT' => 5, 'TYPE_FREESPIN' => 6];
        $qb->createTable('gambling_transactions', QueryBuilder::IF_NOT_EXISTS)
            ->addUInt('id', QueryBuilder::IS_NULLABLE, 64)
            ->addString('external_id', QueryBuilder::IS_NULLABLE)
            ->addDecimal('amount', QueryBuilder::IS_NULLABLE)
            ->addEnum('transaction_type', $gamblingTransactionTypeEnum, QueryBuilder::IS_NULLABLE)
            ->addDecimal('balance_after_transaction', QueryBuilder::IS_NULLABLE)
            ->addUInt('game_session_id', QueryBuilder::IS_NULLABLE, 64)
            ->addDecimal('bonus_amount', QueryBuilder::IS_NULLABLE)
            ->addDecimal('bonus_balance_after_transaction', QueryBuilder::IS_NULLABLE)
            ->addString('bonus_id', QueryBuilder::IS_NULLABLE)
            ->addString('provider_round_id', QueryBuilder::IS_NULLABLE)
            ->addString('provider_transaction_id', QueryBuilder::IS_NULLABLE)
            ->addUInt('created_at', 0, 64)
            ->setEngine($engineMergeTree)
            ->setOrderBy('created_at')
            ->getResult();

        foreach (['gambling_sessions', 'gambling_transactions'] as $tableName) {
            if ((string)$this->getClient()->showCreateTable($tableName) !== '') {
                $this->consoleOut('Table `' . $tableName . '` is created successfully.');
            } else {
                $this->consoleOut('Table `' . $tableName . '` does not exist.');

                return false;
            }
        }

        $qb = $this->createQueryBuilder();
        $qb->createMaterializedView(
            'gambling_sessions_consumer',
            'gambling_sessions',
            QueryBuilder::IF_NOT_EXISTS
        )->selectColumns([
            'id' => $qb->JSONExtractUInt64Safe('message', ['body', 'id']),
            'hall_id' => $qb->JSONExtractUInt64Safe('message', ['header', 'hallId']),
            'hall_external_id' => $qb->toUuid($qb->JSONExtractStringByPath('message', ['header', 'hallExternalId'])),
            'platform_id' => $qb->toUuid($qb->JSONExtractStringByPath('message', ['header', 'platformId'])),
            'player_account_id' => $qb->JSONExtractStringByPath('message', ['body', 'player', 'accountId']),
            'player_id' => $qb->JSONExtractUInt64Safe('message', ['body', 'player', 'id']),
            'player_external_id' => $qb->toUuid(
                $qb->JSONExtractStringByPath('message', ['body', 'player', 'externalId']),
            ),
            'region' => $qb->JSONExtractStringByPath('message', ['body', 'region']),
            'country_code' => $qb->JSONExtractStringByPath('message', ['body', 'countryCode']),
            'ip' => $qb->JSONExtractStringByPath('message', ['body', 'ip']),
            'device' => $qb->ternaryExpression(
                $qb->isIn($qb->JSONExtractStringByPath('message', ['body', 'device']), array_keys($deviceEnum)),
                $qb->JSONExtractStringByPath('message', ['body', 'device']),
                'NULL',
            ),
            'source' => $qb->JSONExtractStringByPath('message', ['body', 'source']),
            'provider_external_id' => $qb->JSONExtractUInt64Safe('message', ['body', 'providerExternalId']),
            'product' => $qb->JSONExtractStringByPath('message', ['body', 'product']),
            'game_type_name' => $qb->JSONExtractStringByPath('message', ['body', 'gameTypeName']),
            'game_mode' => $qb->ternaryExpression(
                $qb->isIn($qb->JSONExtractStringByPath('message', ['body', 'gameMode']), array_keys($gameModeEnum)),
                $qb->JSONExtractStringByPath('message', ['body', 'gameMode']),
                'NULL',
            ),
            'game_external_id' => $qb->JSONExtractUInt64Safe('message', ['body', 'gameExternalId']),
            'token' => $qb->toUuid($qb->JSONExtractStringByPath('message', ['body', 'token'])),
            'currency_code' => $qb->JSONExtractStringByPath('message', ['body', 'currencyCode']),
            'started_at' => $qb->JSONExtractUInt64Safe('message', ['body', 'startDate']),
        ])
            ->from('gambling_sessions_queue')
            ->getResult();

        if ((string)$this->getClient()->showCreateTable('gambling_sessions_consumer') !== '') {
            $this->consoleOut('Table `gambling_sessions_consumer` is created successfully.');
        } else {
            $this->consoleOut('Table `gambling_sessions_consumer` does not exist.');

            return false;
        }

        $qb = $this->createQueryBuilder();
        $qb->createMaterializedView(
            'gambling_transactions_consumer',
            'gambling_transactions',
            QueryBuilder::IF_NOT_EXISTS
        )->selectColumns([
            'id' => $qb->JSONExtractUInt64Safe('message', ['body', 'id']),
            'external_id' => $qb->JSONExtractStringByPath('message', ['body', 'externalId']),
            'amount' => $qb->JSONExtractDecimal128Safe('message', ['body', 'amount']),
            'transaction_type' => $qb->ternaryExpression(
                $qb->isIn(
                    $qb->JSONExtractStringByPath('message', ['body', 'type']),
                    array_keys($gamblingTransactionTypeEnum)
                ),
                $qb->JSONExtractStringByPath('message', ['body', 'type']),
                'NULL',
            ),
            'balance_after_transaction' => $qb->JSONExtractDecimal128Safe(
                'message', ['body', 'balanceAfterTransaction'],
            ),
            'game_session_id' => $qb->JSONExtractUInt64Safe('message', ['body', 'gameSessionId']),
            'bonus_amount' => $qb->JSONExtractDecimal128Safe('message', ['body', 'bonus', 'amount']),
            'bonus_balance_after_transaction' => $qb->JSONExtractDecimal128Safe(
                'message',
                ['body', 'bonus', 'balanceAfterTransaction'],
            ),
            'bonus_id' => $qb->JSONExtractStringByPath('message', ['body', 'bonus', 'id']),
            'provider_round_id' => $qb->JSONExtractStringByPath('message', ['body', 'providerRoundId']),
            'provider_transaction_id' => $qb->JSONExtractStringByPath('message', ['body', 'providerTransactionId']),
            'created_at' => $qb->JSONExtractUInt64Safe('message', ['body', 'createdAt']),
        ])
            ->from('gambling_transactions_queue')
            ->getResult();

        if ((string)$this->getClient()->showCreateTable('gambling_transactions_consumer') !== '') {
            $this->consoleOut('Table `gambling_transactions_consumer` is created successfully.');
        } else {
            $this->consoleOut('Table `gambling_transactions_consumer` does not exist.');

            return false;
        }

        return true;
    }

    private function createBettingTables(): bool
    {
        $config = $this->getMigrationConfig();

        $qb = $this->createQueryBuilder();
        $engine = new EngineKafka();
        $engine->setConnectionSettings([
            'kafka_broker_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_BROKER_LIST'],
            'kafka_topic_list' => $config['CLICKHOUSE_MIGRATION_KAFKA_BETTING_TOPIC'],
            'kafka_group_name' => sprintf('%s_clickhouse', $config['CLICKHOUSE_MIGRATION_KAFKA_BETTING_TOPIC']),
            'kafka_format' => 'JSONAsString',
            'kafka_num_consumers' => 1
        ]);

        $qb->createTable('betting_transactions_queue', QueryBuilder::IF_NOT_EXISTS)
            ->addString('message')
            ->setEngine($engine)
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable(
                'betting_transactions_queue',
            ) !== ''
        ) {
            $this->consoleOut('Table `betting_transactions_queue` is created successfully.');
        } else {
            $this->consoleOut('Table `betting_transactions_queue` does not exist.');

            return false;
        }

        $transaction_type = ['bet' => 1, 'win' => 2, 'cancel' => 3, 'rollback' => 4, 'reject' => 5];

        $bet_type = ['single' => 1, 'accumulator' => 2, 'anti_accumulator' => 3, 'system' => 4,
            'chain' => 5, 'multi' => 6, 'advance' => 7, 'conditional' => 8, 'lucky' => 9,
            'patent' => 10, 'asian_handicap_single' => 11, 'asian_handicap_accumulator_part' => 12,
            'asian_handicap_total' => 13];

        $engine = new EngineMergeTree($config['CLICKHOUSE_MIGRATION_ENGINE_TABLE_BETTING']);

        $qb = $this->createQueryBuilder();
        $qb->createTable('betting_transactions', QueryBuilder::IF_NOT_EXISTS)
            ->addDateTime('timestamp')
            ->addUuid('casino_id')
            ->addUuid('hall_id')
            ->addUuid('player_id')
            ->addString('currency_code')
            ->addDecimal('amount')
            ->addUuid('transaction_id')
            ->addEnum('transaction_type', $transaction_type, QueryBuilder::IS_NULLABLE)
            ->addUuid('bet_id')
            ->addUuid('coupon_id')
            ->addInt('sport_id')
            ->addInt('game_id')
            ->addInt('market_type')
            ->addEnum('bet_type', $bet_type)
            ->addString('country_code', QueryBuilder::IS_NULLABLE)
            ->setEngine($engine)
            ->setOrderBy('timestamp')
            ->getResult();

        if ((string)$this->getClient()->showCreateTable('betting_transactions') !== '') {
            $this->consoleOut('Table `betting_transactions` is created successfully.');
        } else {
            $this->consoleOut('Table `betting_transactions` does not exist.');

            return false;
        }

        $qb = $this->createQueryBuilder();
        $qb->createMaterializedView(
            'betting_transactions_consumer',
            'betting_transactions',
            QueryBuilder::IF_NOT_EXISTS
        )
            ->selectColumns(
                [
                    'timestamp' => $qb->fromUnixTime($qb->toUInt64($qb->JSONExtractRaw('message', 'timestamp'))),
                    'casino_id' => $qb->JSONExtractString('message', 'casino_id'),
                    'hall_id' => $qb->JSONExtractString('message', 'hall_id'),
                    'player_id' => $qb->JSONExtractString('message', 'player_id'),
                    'currency_code' => $qb->JSONExtractString('message', 'currency_code'),
                    'amount' => $qb->toDecimal128($qb->JSONExtractString('message', 'amount')),
                    'transaction_id' => $qb->JSONExtractString('message', 'transaction_id'),
                    'transaction_type' => $qb->JSONExtractString('message', 'transaction_type'),
                    'country_code' => $qb->JSONExtractString('message', 'country_code'),
                    'bet_id' => $qb->JSONExtractString('message', 'bet_id'),
                    'coupon_id' => $qb->JSONExtractString('message', 'coupon_id'),
                    'sport_id' => $qb->JSONExtractUInt('message', 'sport_id'),
                    'game_id' => $qb->JSONExtractUInt('message', 'game_id'),
                    'market_type' => $qb->JSONExtractUInt('message', 'market_type'),
                    'bet_type' => $qb->JSONExtractString('message', 'bet_type'),
                ]
            )
            ->from('betting_transactions_queue')
            ->getResult();

        if (
            (string)$this->getClient()->showCreateTable('betting_transactions_consumer') !== ''
        ) {
            $this->consoleOut('Table `betting_transactions_consumer` is created successfully.');
        } else {
            $this->consoleOut('Table `betting_transactions_consumer` does not exist.');

            return false;
        }

        return true;
    }
}
