<?php

declare(strict_types=1);

namespace App\Clickhouse\Migration;

interface ClickhouseMigrationInterface
{
    public function up(): bool;

    public function down(): bool;

    public function getVersion(): string;

    public function setOutput(MigrationOutputInterface $output): void;
}
