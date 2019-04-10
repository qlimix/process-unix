<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal\Pcntl;

use Qlimix\Process\Runtime\Signal\Dispatcher;
use Qlimix\Process\Runtime\Signal\Exception\SignalException;
use function pcntl_signal_dispatch;

final class PcntlDispatcher implements Dispatcher
{
    /**
     * @inheritDoc
     */
    public function dispatch(): void
    {
        if (!pcntl_signal_dispatch()) {
            throw new SignalException('Failed to dispatch pcntl signals');
        }
    }
}
