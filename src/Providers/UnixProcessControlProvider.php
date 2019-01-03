<?php declare(strict_types=1);

namespace Qlimix\Process\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\DependencyProviderInterface;
use Qlimix\DependencyContainer\DependencyRegistryInterface;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\ProcessControlInterface;
use Qlimix\Process\Runtime\PcntlRuntimeControl;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\UnixProcessControl;
use const SIGTERM;
use const SIGINT;
use const SIGHUP;
use const SIGABRT;

final class UnixProcessControlProvider implements DependencyProviderInterface
{
    /**
     * @inheritDoc
     */
    public function provide(DependencyRegistryInterface $registry): void
    {
        $registry->set(ProcessControlInterface::class, static function (ContainerInterface $container) {
            return new UnixProcessControl(
                $container->get(RuntimeControlInterface::class),
                $container->get(OutputInterface::class)
            );
        });

        $registry->set(RuntimeControlInterface::class, static function (ContainerInterface $container) {
            return new PcntlRuntimeControl([
                SIGTERM,
                SIGINT,
                SIGHUP,
                SIGABRT
            ]);
        });
    }
}
