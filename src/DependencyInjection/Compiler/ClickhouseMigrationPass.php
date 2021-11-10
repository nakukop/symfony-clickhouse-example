<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Clickhouse\Migration\ClickhouseMigrationRunner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ClickhouseMigrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ClickhouseMigrationRunner::class)) {
            return;
        }

        $definition = $container->findDefinition(ClickhouseMigrationRunner::class);
        $migrations = $container->findTaggedServiceIds(ClickhouseMigrationRunner::SERVICE_TAG);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        foreach ($migrations as $id => $tags) {
            $definition->addMethodCall('addMigration', [new Reference($id)]);
        }
    }
}
