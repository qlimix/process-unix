<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime;

use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Runtime\Signal\Dispatcher;
use Throwable;

final class UnixRuntimeControl implements RuntimeControlInterface
{
    /** @var Dispatcher */
    private $dispatcher;

    /** @var OutputInterface */
    private $output;

    /** @var bool */
    private $quit = false;

    public function __construct(Dispatcher $dispatcher, OutputInterface $output)
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
            $this->output->write('Failed to dispatch');
            $this->output->write((string) $exception);
            $this->quit();
        }
    }

    /**
     * @inheritdoc
     */
    public function abort(): bool
    {
        return $this->quit;
    }

    public function quit(): void
    {
        $this->quit = true;
    }
}
