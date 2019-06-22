<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal\Pcntl;

use Qlimix\Process\Runtime\Signal\DispatcherInterface;
use Qlimix\Process\Runtime\Signal\Exception\DispatcherException;
use function pcntl_signal_dispatch;

final class PcntlDispatcher implements DispatcherInterface
{
    /**
     * @inheritDoc
     */
    public function dispatch(): void
    {
        if (!pcntl_signal_dispatch()) {
            throw new DispatcherException('Failed to dispatch pcntl signals');
        }
    }
}
