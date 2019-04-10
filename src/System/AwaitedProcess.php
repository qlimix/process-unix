<?php declare(strict_types=1);

namespace Qlimix\Process\System;

final class AwaitedProcess
{
    /** @var int */
    private $pid;

    /** @var int */
    private $status;

    public function __construct(int $pid, int $status)
    {
        $this->pid = $pid;
        $this->status = $status;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
