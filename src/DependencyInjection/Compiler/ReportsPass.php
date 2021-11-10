<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Service\ReportFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReportsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ReportFactory::class)) {
            return;
        }

        $definition = $container->findDefinition(ReportFactory::class);
        $services = $container->findTaggedServiceIds(ReportFactory::SERVICE_TAG);

        foreach (array_keys($services) as $id) {
            $definition->addMethodCall('addReport', [new Reference($id)]);
        }
    }
}
