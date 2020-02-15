<?php declare(strict_types=1);

namespace Qlimix\Process\System;

use Qlimix\Process\System\Exception\SystemException;
use function posix_kill;
use function proc_close;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use const SIGKILL;
use const SIGTERM;
use const STDERR;
use const STDIN;
use const STDOUT;

final class ProcSystem implements SystemInterface
{
    /** @var resource[] */
    private array $procs = [];

    /**
     * @inheritDoc
     */
    public function status(): ?Status
    {
        foreach ($this->procs as $index => $proc) {
            $status = proc_get_status($proc);
            if (!$status['running']) {
                proc_close($proc);
                unset($this->procs[$index]);

                return new Status($status['pid'], $status['exitcode']);
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function kill(int $pid): void
    {
        if (!isset($this->procs[$pid])) {
            throw new SystemException('Unknown pid to kill');
        }

        if (!proc_terminate($this->procs[$pid], SIGKILL)) {
            throw new SystemException('Failed to kill process');
        }
    }

    /**
     * @inheritDoc
     */
    public function terminate(int $pid): void
    {
        if (!isset($this->procs[$pid])) {
            throw new SystemException('Unknown pid to terminate');
        }

        if (!proc_terminate($this->procs[$pid], SIGTERM)) {
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function spawn(string $process): int
    {
        $proc = proc_open(
            'exec '.$process,
            [STDIN, STDOUT, STDERR],
            $pipes
        );

        if ($proc === false) {
            throw new SystemException('Failed to spawn process');
        }

        $status = proc_get_status($proc);

        $processId = $status['pid'];
        $this->procs[$processId] = $proc;

        return $processId;
    }
}
