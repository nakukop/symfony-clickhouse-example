<?php

declare(strict_types=1);

namespace App\DB;

use ClickHouseDB\Client;

class Connection
{
    private Client $connection;

    /**
     * @param array<string, string> $config
     */
    public function __construct(array $config = [])
    {
        $this->connection = new Client($config);
        $this->connection->database($config['database']);
        $this->connection->enableQueryConditions();
    }

    public function getConnection(): Client
    {
        return $this->connection;
    }
}
