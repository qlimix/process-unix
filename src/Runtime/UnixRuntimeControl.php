<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime;

use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Runtime\Signal\DispatcherInterface;
use Throwable;

final class UnixRuntimeControl implements RuntimeControlInterface
{
    private DispatcherInterface $dispatcher;

    private OutputInterface $output;

    private bool $quit = false;

    public function __construct(DispatcherInterface $dispatcher, OutputInterface $output)
    {
        $this->dispatcher = $dispatcher;
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function tick(): void
    {
        try {
            $this->dispatcher->dispatch();
        } catch (Throwable $exception) {
            if (!$this->quit) {
                $this->quit(Reason::fromException($exception));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function abort(): bool
    {
        return $this->quit;
    }

    public function quit(Reason $reason): void
    {
        $this->quit = true;
        $this->output->writeLine('Quiting, reason:');
        $this->output->writeLine($reason->getMessage());
    }
}
