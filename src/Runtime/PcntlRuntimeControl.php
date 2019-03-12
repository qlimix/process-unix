<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime;

final class PcntlRuntimeControl implements RuntimeControlInterface
{
    /** @var bool */
    private $quit = false;

    /**
     * @param int[] $signals
     */
    public function __construct(array $signals)
    {
        foreach ($signals as $signal) {
            $this->registerSignal($signal);
        }
    }

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

    private function registerSignal(int $signal): void
    {
        pcntl_signal($signal, [$this, 'signal']);
    }

    public function signal($signal): void
    {
        $this->quit = true;
    }
}
