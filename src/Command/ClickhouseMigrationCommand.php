<?php

declare(strict_types=1);

namespace App\Command;

use App\Clickhouse\Migration\ClickhouseMigrationRunner;
use App\Clickhouse\Migration\MigrationOutputConsoleAdapter;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class ClickhouseMigrationCommand extends Command
{
    private const OPTION_ROLLBACK_VERSION_NAME = 'v';

    private const ARGUMENT_ROLLBACK_FLAG_NAME = 'down';

    protected ClickhouseMigrationRunner $migrationRunner;

    /**
     * @inheritDoc
     * @var string $defaultName
     */
    protected static $defaultName = 'clickhouse:migrate';

    public function __construct(ClickhouseMigrationRunner $migrationRunner)
    {
        $this->migrationRunner = $migrationRunner;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_ROLLBACK_FLAG_NAME, InputArgument::OPTIONAL)
            ->addOption(self::OPTION_ROLLBACK_VERSION_NAME, null, InputOption::VALUE_REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrationRunner->setOutput((new MigrationOutputConsoleAdapter())->setOutput($output));

        $isRollback = $input->getArgument(self::ARGUMENT_ROLLBACK_FLAG_NAME) === self::ARGUMENT_ROLLBACK_FLAG_NAME;

        try {
            if ($isRollback) {
                $this->migrationRunner->runDown($input->getOption(self::OPTION_ROLLBACK_VERSION_NAME));
            } else {
                if (!in_array($input->getOption(self::OPTION_ROLLBACK_VERSION_NAME), [null, false], true)) {
                    throw new Exception(
                        'Incorrect command syntax: option `--v` can be used after `down` argument only.',
                    );
                }

                $this->migrationRunner->runUp();
            }
        } catch (Throwable $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
