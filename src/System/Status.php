<?php declare(strict_types=1);

namespace Qlimix\Process\System;

final readonly class Status
{
    public function __construct(public int $id, public int $exitCode)
    {
    }
}
