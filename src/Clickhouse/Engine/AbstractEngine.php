<?php

declare(strict_types=1);

namespace App\Clickhouse\Engine;

use App\Clickhouse\Engine\Exception\EngineBaseException;

abstract class AbstractEngine implements EngineInterface
{
    public const ENGINE_TYPE = '';

    /**
     * @var array<string, string|int|bool|null>
     */
    protected array $connectionSettings = [];

    private string $confString = '';

    abstract public function getConnectionDsnString(): string;

    /**
     * @inheritDoc
     */
    abstract public function setConnectionSettings(array $settings): void;

    abstract public function canUseConnectionSettings(): bool;

    /**
     * @throws EngineBaseException
     */
    public function __construct(?string $envString = null)
    {
        if ($envString !== null) {
            $this->setConfString(
                trim($envString),
            );
        }
    }

    /**
     * @throws EngineBaseException
     */
    public function setConfString(string $confString): void
    {
        $validConfString = $this->createValidConfString($confString);

        if ($validConfString !== null) {
            $this->confString = $validConfString;

            return;
        }

        throw new EngineBaseException(EngineBaseException::INVALID_ENGINE_TYPE . '. Check string: ' . $confString);
    }

    public function isValidConfString(): bool
    {
        if (strlen($this->confString) > 0) {
            foreach ([$this->getEngineType(), ...$this->getSubtypes()] as $correctType) {
                if (str_contains($this->confString, $correctType)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getConfString(): string
    {
        return $this->confString;
    }

    public function getConnectionSettingsString(): string
    {
        return implode(
            ', ',
            array_map(
                fn($key, $value) => sprintf("%s = '%s'", $key, (string)$value),
                array_keys($this->connectionSettings),
                $this->connectionSettings,
            ),
        );
    }

    public function getEngineType(): string
    {
        return static::ENGINE_TYPE;
    }

    /**
     * @return array<int, string>
     */
    public function getSubtypes(): array
    {
        return [];
    }

    private function createValidConfString(string $confString): ?string
    {
        $cleanType = strstr($confString, '(', true);

        foreach ([$this->getEngineType(), ...$this->getSubtypes()] as $correctType) {
            if (strtolower($cleanType) === strtolower($correctType)) {
                return str_replace($cleanType, $correctType, $confString);
            }
        }

        return null;
    }
}
