<?php declare(strict_types=1);

namespace Qlimix\Process;

use Qlimix\Process\Exception\ProcessException;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Registry\RegistryInterface;
use Qlimix\Process\Result\ExitedProcess;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\System\SystemInterface;
use Qlimix\Process\Terminate\TerminationInterface;
use Throwable;

final class UnixProcessControl implements ProcessControlInterface
{
    /** @var RuntimeControlInterface */
    private $control;

    /** @var SystemInterface */
    private $system;

    /** @var OutputInterface */
    private $output;

    /** @var RegistryInterface */
    private $registry;

    /** @var TerminationInterface */
    private $termination;

    public function __construct(
        RuntimeControlInterface $control,
        SystemInterface $system,
        OutputInterface $output,
        RegistryInterface $registry,
        TerminationInterface $termination
    ) {
        $this->control = $control;
        $this->system = $system;
        $this->output = $output;
        $this->registry = $registry;
        $this->termination = $termination;
    }

    /**
     * @inheritDoc
     */
    public function status(): ?ExitedProcess
    {
        $this->output->write('Reaping');
        try {
            $awaitedProcess = $this->system->wait();
        } catch (Throwable $exception) {
            throw new ProcessException('Failed waiting for a returning process', 0, $exception);
        }

        if ($awaitedProcess === null) {
            $this->output->write('No child returned');
            return null;
        }

        $this->output->write('Found returned process');
        try {
            return new ExitedProcess(
                $this->registry->remove($awaitedProcess->getPid()),
                $awaitedProcess->getStatus() === 0
            );
        } catch (Throwable $exception) {
            throw new ProcessException('Couldn\'t find pid in process list');
        }
    }

    /**
     * @inheritDoc
     */
    public function isProcessRunning(int $pid): bool
    {
        if (!$this->registry->has($pid)) {
            return false;
        }

        try {
            return $this->system->alive($pid);
        } catch (Throwable $exception) {
            throw new ProcessException('Failed to check if process is still running', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function startProcess(ProcessInterface $process): int
    {
        $this->output->write('Forking');

        try {
            $pid = $this->system->spawn();
        } catch (Throwable $exception) {
            throw new ProcessException('Could not start new process', 0, $exception);
        }

        if ($pid > 0) {
            return $this->registry->add($pid, $process);
        }

        try {
            $process->run($this->control, $this->output);
        } catch (Throwable $exception) {
            $this->termination->fail();
        }

        $this->termination->success();

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function startProcesses(array $processes): array
    {
        $pids = [];
        foreach ($processes as $process) {
            $pids[] = $this->startProcess($process);
        }

        return $pids;
    }

    /**
     * @inheritDoc
     */
    public function stopProcess(int $pid): void
    {
        if (!$this->registry->has($pid)) {
            return;
        }

        try {
            $this->system->terminate($pid);
        } catch (Throwable $exception) {
            throw new ProcessException('Failed to stop process', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function stopProcesses(): void
    {
        $this->output->write('Stop processes');
        foreach ($this->registry->getAll() as $process) {
            try {
                $this->system->terminate($process->getProcessId());
            } catch (Throwable $exception) {
                $this->output->write('Failed to stop process');
            }
        }
    }
}
