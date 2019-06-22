<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal\Pcntl;

use Qlimix\Process\Runtime\Signal\Exception\DispatcherException;
use Qlimix\Process\Runtime\Signal\Exception\SignalException;
use Qlimix\Process\Runtime\Signal\HandlerInterface;
use Qlimix\Process\Runtime\Signal\HandlerRegistryInterface;
use function count;
use function pcntl_signal;

final class PcntlHandlerRegistry implements HandlerRegistryInterface
{
    /** @var HandlerInterface[][] */
    private $handlers = [];

    /**
     * @inheritDoc
     */
    public function register(int $signal, HandlerInterface $handler): void
    {
        $this->handlers[$signal][] = $handler;

        if (count($this->handlers[$signal]) > 1) {
            return;
        }

        if (!pcntl_signal($signal, [$this, 'handle'])) {
            throw new DispatcherException('Failed to register pcntl sign handler');
        }
    }

    /**
     * @param mixed $signinfo
     *
     * @throws SignalException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function handle(int $signo, $signinfo): void
    {
        if (!isset($this->handlers[$signo])) {
            return;
        }

        foreach ($this->handlers[$signo] as $handler) {
            $handler->handle();
        }
    }
}
