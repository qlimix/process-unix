<?php declare(strict_types=1);

namespace Qlimix\Process\Providers;

use Psr\Container\ContainerInterface;
use Qlimix\DependencyContainer\ProviderInterface;
use Qlimix\DependencyContainer\RegistryInterface;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\ProcessControlInterface;
use Qlimix\Process\Runtime\PcntlRuntimeControl;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\UnixProcessControl;
use const SIGABRT;
use const SIGHUP;
use const SIGINT;
use const SIGTERM;

final class UnixProcessControlProvider implements ProviderInterface
{
    /**
     * @inheritDoc
     */
    public function provide(RegistryInterface $registry): void
    {
        $registry->set(ProcessControlInterface::class, static function (ContainerInterface $container) {
            return new UnixProcessControl(
                $container->get(RuntimeControlInterface::class),
                $container->get(OutputInterface::class)
            );
        });

        $registry->set(RuntimeControlInterface::class, static function () {
            return new PcntlRuntimeControl([
                SIGTERM,
                SIGINT,
                SIGHUP,
                SIGABRT,
            ]);
        });
    }
}
