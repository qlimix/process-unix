<?php declare(strict_types=1);

namespace Qlimix\Process\System;

use Qlimix\Process\System\Exception\SystemException;
use Qlimix\Process\Terminate\TerminationInterface;
use function explode;
use function pcntl_exec;
use function pcntl_fork;
use function pcntl_wait;
use function pcntl_wexitstatus;
use function posix_kill;
use const SIGKILL;
use const SIGTERM;
use const WNOHANG;

final class ForkSystem implements SystemInterface
{
    private TerminationInterface $termination;

    public function __construct(TerminationInterface $termination)
    {
        $this->termination = $termination;
    }

    /**
     * @inheritDoc
     */
    public function status(): ?Status
    {
        $pid = pcntl_wait($status, WNOHANG);

        if ($pid === 0) {
            return null;
        }

        if ($pid === -1) {
            throw new SystemException('Failed waiting for a returning process');
        }

        return new Status($pid, pcntl_wexitstatus($status));
    }

    /**
     * @inheritDoc
     */
    public function kill(int $pid): void
    {
        if (!posix_kill($pid, SIGKILL)) {
            throw new SystemException('Failed to kill process');
        }
    }

    /**
     * @inheritDoc
     */
    public function terminate(int $pid): void
    {
        if (!posix_kill($pid, SIGTERM)) {
            throw new SystemException('Failed to terminate process');
        }
    }

    /**
     * @inheritDoc
     */
    public function alive(int $pid): bool
    {
        return posix_kill($pid, 0);
    }

    /**
     * @inheritDoc
     */
    public function spawn(string $process): int
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new SystemException('Could not spawn new process');
        }

        if ($pid > 0) {
            return $pid;
        }

        $args = explode(' ', $process);
        $process = $args[0];
        unset($args[0]);

        $exec = pcntl_exec($process, $args);
        if ($exec !== null) {
            $this->termination->fail(1);
        }

        return 0;
    }
}
