<?php declare(strict_types=1);

namespace Qlimix\Process\System;

use Qlimix\Process\System\Exception\SystemException;
use function pcntl_fork;
use function pcntl_wait;
use function posix_kill;
use const SIGKILL;
use const SIGTERM;
use const WNOHANG;

final class UnixSystem implements SystemInterface
{
    /**
     * @inheritDoc
     */
    public function kill(int $pid): void
    {
        if (posix_kill($pid, SIGKILL)) {
            throw new SystemException('Failed to kill process');
        }
    }

    /**
     * @inheritDoc
     */
    public function terminate(int $pid): void
    {
        if (posix_kill($pid, SIGTERM)) {
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
    public function spawn(): int
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new SystemException('Could not spawn new process');
        }

        return $pid;
    }

    /**
     * @inheritDoc
     */
    public function wait(): ?AwaitedProcess
    {
        $status = -1;
        $pid = pcntl_wait($status, WNOHANG);

        if ($pid === 0) {
            return null;
        }

        if ($pid === -1) {
            throw new SystemException('Failed waiting for a returning process');
        }

        return new AwaitedProcess($pid, $status);
    }
}
