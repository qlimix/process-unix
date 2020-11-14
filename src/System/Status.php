<?php declare(strict_types=1);

namespace Qlimix\Process\System;

final class Status
{
    private int $id;

    private int $exitCode;

    public function __construct(int $id, int $exitCode)
    {
        $this->id = $id;
        $this->exitCode = $exitCode;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
