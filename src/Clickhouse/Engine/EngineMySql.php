<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine;

use App\Clickhouse\Engine\Exception\EngineBaseException;

final class EngineMySql extends AbstractEngine implements EngineInterface
{
    //MySql engine can be used as engine for whole db (CREATE DATABASE) or tables (CREATE TABLE).
    public const ENGINE_MODE_TABLE = 1;

    public const ENGINE_MODE_DB = 2;

    public const ENGINE_TYPE = 'MySQL';

    private int $mode = self::ENGINE_MODE_TABLE;

    private ?string $host;

    private ?string $port;

    private ?string $database;

    private ?string $table;

    private ?string $user;

    private ?string $password;

    public function __construct(int $mode = self::ENGINE_MODE_TABLE, ?string $envString = null)
    {
        if (in_array($mode, [self::ENGINE_MODE_TABLE, self::ENGINE_MODE_DB], true)) {
            $this->mode = $mode;
        }

        parent::__construct($envString);
    }

    public function getConnectionDsnString(): string
    {
        if ($this->isValidConfString()) {
            return $this->getConfString();
        }

        if (
            $this->host === null
            || $this->port === null
            || $this->database === null
            || $this->user === null
            || $this->password === null
            || ($this->mode === self::ENGINE_MODE_TABLE && $this->table === null)
        ) {
            $message = 'Check parameters for DSN: host, port, database, user, password';
            $message .= $this->mode === self::ENGINE_MODE_TABLE ? ', table.' : '.';

            throw new EngineBaseException($message);
        }

        if ($this->mode === self::ENGINE_MODE_TABLE) {
            return sprintf(
                "%s('%s:%s', '%s', '%s', '%s', '%s')",
                $this->getEngineType(),
                $this->getHost(),
                $this->getPort(),
                $this->getDatabase(),
                $this->getTable(),
                $this->getUser(),
                $this->getPassword(),
            );
        }

        return sprintf(
            "%s('%s:%s', '%s', '%s', '%s')",
            $this->getEngineType(),
            $this->getHost(),
            $this->getPort(),
            $this->getDatabase(),
            $this->getUser(),
            $this->getPassword(),
        );
    }

    /**
     * @inheritDoc
     */
    public function setConnectionSettings(array $settings): void
    {
        unset($settings);

        throw new EngineBaseException(EngineBaseException::UNAVAILABLE_USING_CONNECTION_SETTINGS);
    }

    public function canUseConnectionSettings(): bool
    {
        return false;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function setDatabase(string $database): void
    {
        $this->database = $database;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
