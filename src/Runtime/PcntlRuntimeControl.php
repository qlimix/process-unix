<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime;

final class PcntlRuntimeControl implements RuntimeControlInterface
{
    /** @var bool */
    private $quit = false;

    /**
     * @inheritdoc
     */
    public function tick(): void
    {
        pcntl_signal_dispatch();
    }

    /**
     * @inheritdoc
     */
    public function abort(): bool
    {
        return $this->quit;
    }

    public function init(): void
    {
        pcntl_signal(SIGHUP, [$this, 'signal']);
        pcntl_signal(SIGTERM, [$this, 'signal']);
        pcntl_signal(SIGINT, [$this, 'signal']);
        pcntl_signal(SIGQUIT, [$this, 'signal']);
    }

    public function signal($signal): void
    {
        $this->quit = true;
    }
}
