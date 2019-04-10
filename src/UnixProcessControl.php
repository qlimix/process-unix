<?php declare(strict_types=1);

namespace Qlimix\Process;

use Qlimix\Process\Exception\ProcessException;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Result\ExitedProcess;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\System\SystemInterface;
use Throwable;

final class UnixProcessControl implements ProcessControlInterface
{
    /** @var RuntimeControlInterface */
    private $control;

    /** @var SystemInterface */
    private $system;

    /** @var OutputInterface */
    private $output;

    /** @var int[] */
    private $processes = [];

    /** @var int */
    private $nextPid = 0;

    public function __construct(RuntimeControlInterface $control, OutputInterface $output)
    {
        $this->control = $control;
        $this->output = $output;
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
        foreach ($this->processes as $index => $process) {
            if ($process === $awaitedProcess->getPid()) {
                unset($this->processes[$index]);
                return new ExitedProcess($index, $awaitedProcess->getStatus() === 0);
            }
        }

        throw new ProcessException('Couldn\'t find pid in process list');
    }

    /**
     * @inheritDoc
     */
    public function isProcessRunning(int $pid): bool
    {
        if (!isset($this->processes[$pid])) {
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
            $this->nextPid++;
            $this->processes[$this->nextPid] = $pid;
            return $this->nextPid;
        }

        try {
            $process->run($this->control, $this->output);
        } catch (Throwable $exception) {
            exit(1);
        }

        exit(0);
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
        if (!isset($this->processes[$pid])) {
            return;
        }

        try {
            $this->system->kill($pid);
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
        foreach ($this->processes as $process) {
            try {
                $this->system->kill($process);
            } catch (Throwable $exception) {
                $this->output->write('Failed to stop process');
            }
        }
    }
}
