<?php

declare(strict_types=1);

namespace App\Clickhouse\Migration;

interface MigrationOutputInterface
{
    /**
     * Writes a message to the output.
     *
     * @param string|array<string> $messages The message as an iterable of strings or a single string
     * @param bool            $newline  Whether to add a newline
     */
    public function write(string|array $messages, bool $newline = false): void;

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array<string> $messages The message as an iterable of strings or a single string
     */
    public function writeln(string|array $messages): void;
}
