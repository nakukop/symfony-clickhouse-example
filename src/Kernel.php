<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\Compiler\ClickhouseMigrationPass;
use App\DependencyInjection\Compiler\ReportRequestFilterPass;
use App\DependencyInjection\Compiler\ReportsPass;
use LogicException;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function dirname;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private ?string $projectDir = null;

    /**
     * @inheritDoc
     */
    public function getProjectDir()
    {
        if ($this->projectDir === null) {
            $r = new ReflectionObject($this);
            $dir = $r->getFileName();

            if (!is_file($dir)) {
                throw new LogicException(
                    sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name),
                );
            }

            $this->projectDir = dirname($dir, 2);
        }

        return $this->projectDir;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        } elseif (is_file($path = dirname(__DIR__) . '/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = dirname(__DIR__) . '/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container
            ->addCompilerPass(new ReportsPass())
            ->addCompilerPass(new ClickhouseMigrationPass())
            ->addCompilerPass(new ReportRequestFilterPass())
        ;
    }
}
