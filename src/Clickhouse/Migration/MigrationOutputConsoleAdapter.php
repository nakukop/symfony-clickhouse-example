<?php

declare(strict_types=1);

namespace App\Clickhouse\Migration;

use Symfony\Component\Console\Output\OutputInterface;

class MigrationOutputConsoleAdapter implements MigrationOutputInterface
{
    private OutputInterface $consoleOutput;

    public function setOutput(OutputInterface $output): MigrationOutputConsoleAdapter
    {
        $this->consoleOutput = $output;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function write($messages, bool $newline = false): void
    {
        $this->consoleOutput->write($messages, $newline);
    }

    /**
     * @inheritDoc
     */
    public function writeln($messages): void
    {
        $this->consoleOutput->writeln($messages);
    }
}
