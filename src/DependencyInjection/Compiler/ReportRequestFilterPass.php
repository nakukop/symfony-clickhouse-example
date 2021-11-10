<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Service\ReportRequestFilterFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ReportRequestFilterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ReportRequestFilterFactory::class)) {
            return;
        }

        $definition = $container->findDefinition(ReportRequestFilterFactory::class);
        $services = $container->findTaggedServiceIds(ReportRequestFilterFactory::SERVICE_TAG);

        foreach (array_keys($services) as $id) {
            $definition->addMethodCall('addFilter', [new Reference($id)]);
        }
    }
}
