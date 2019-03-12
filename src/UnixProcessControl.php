<?php declare(strict_types=1);

namespace Qlimix\Process;

use Qlimix\Process\Exception\ProcessException;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Throwable;

final class UnixProcessControl implements ProcessControlInterface
{
    /** @var RuntimeControlInterface */
    private $control;

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
    public function status(): ?int
    {
        $status = -1;
        $this->output->write('Reaping');
        $pid = pcntl_wait($status, WNOHANG);

        if ($pid === 0) {
            $this->output->write('No child returned');
            return null;
        }

        if ($pid === -1) {
            throw new ProcessException('Failed waiting for a returning process');
        }

        if (pcntl_wifexited($status)) {
            $this->output->write('Found returned process');
            foreach ($this->processes as $index => $process) {
                if ($process === $pid) {
                    unset($this->processes[$index]);
                    return $index;
                }
            }
            throw new ProcessException('Couldn\'t find pid in process list');
        }

        throw new ProcessException('Process returned with '.pcntl_wexitstatus($status));
    }

    /**
     * @inheritDoc
     */
    public function isProcessRunning(int $pid): bool
    {
        if (!isset($this->processes[$pid])) {
            return false;
        }

        $status = -1;
        $this->output->write('Reaping');
        $pid = pcntl_waitpid($pid, $status, WNOHANG);

        if ($pid === 0) {
            $this->output->write('No child returned');
            return true;
        }

        if ($pid === -1) {
            throw new ProcessException('Failed waiting for a returning process');
        }

        if (pcntl_wifexited($status)) {
            $this->output->write('Found returned process');
            foreach ($this->processes as $index => $process) {
                if ($process === $pid) {
                    unset($this->processes[$index]);
                    return false;
                }
            }
            throw new ProcessException('Couldn\'t find pid in process list');
        }

        throw new ProcessException('Process returned with '.pcntl_wexitstatus($status));
    }

    /**
     * @inheritDoc
     */
    public function startProcess(ProcessInterface $process): int
    {
        $this->output->write('Forking');
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new ProcessException('Could not start new process');
        }

        if ($pid > 0) {
            $internalPid = $this->nextPid++;
            $this->processes[$internalPid] = $pid;
            return $internalPid;
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

        posix_kill($pid, SIGKILL);
    }

    /**
     * @inheritDoc
     */
    public function stopProcesses(): void
    {
        $this->output->write('Stop processes');
        foreach ($this->processes as $process) {
            posix_kill($process, SIGKILL);
        }
    }
}
