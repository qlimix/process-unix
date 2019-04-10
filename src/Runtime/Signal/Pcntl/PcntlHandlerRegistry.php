<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal\Pcntl;

use Qlimix\Process\Runtime\Signal\Exception\SignalException;
use Qlimix\Process\Runtime\Signal\HandlerInterface;
use Qlimix\Process\Runtime\Signal\HandlerRegistryInterface;
use function pcntl_signal;

final class PcntlHandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @inheritDoc
     */
    public function register(int $signal, HandlerInterface $handler): void
    {
        if (!pcntl_signal($signal, [$handler, 'handle'])) {
            throw new SignalException('Failed to register pcntl sign handler');
        }
    }
}
