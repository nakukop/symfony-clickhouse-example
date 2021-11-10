<?php

declare(strict_types=1);

namespace App\Clickhouse\DictionarySource;

class DictionarySourceMySql implements DictionarySourceInterface
{
    private string $type = 'MYSQL';

    private string $remoteTable;

    /**
     * @var array<string>
     */
    private array $hosts;

    private string $db;

    private int $port;

    private string $user;

    private string $password;

    private string $layout = 'HASHED()';

    private int $lifeTime = 300;

    /**
     * @param array<string> $host
     */
    public function __construct(
        string $remoteTable,
        array $host,
        string $db,
        int $port,
        string $user,
        string $password,
    ) {
        $this->remoteTable = $remoteTable;
        $this->hosts = $host;
        $this->db = $db;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string>
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    public function getDb(): string
    {
        return $this->db;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRemoteTable(): string
    {
        return $this->remoteTable;
    }

    public function getSourceConnectionString(): string
    {
        return sprintf(
            "%s(
                %s
                port %d
                db '%s'
                table '%s'
                user '%s'
                password '%s'
                fail_on_connection_loss 'true'
            )",
            $this->type,
            $this->getReplicasString(),
            $this->getPort(),
            $this->getDb(),
            $this->getRemoteTable(),
            $this->getUser(),
            $this->getPassword(),
        );
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function setLifeTime(int $lifeTime): void
    {
        $this->lifeTime = $lifeTime;
    }

    public function getLifetime(): int
    {
        return $this->lifeTime;
    }

    private function getReplicasString(): string
    {
        $string = '';

        foreach ($this->getHosts() as $index => $host) {
            $string .= sprintf(" replica(host '%s' priority %d) ", $host, (int)$index + 1);
        }

        return $string;
    }
}
