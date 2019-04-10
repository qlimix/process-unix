<?php declare(strict_types=1);

namespace Qlimix\Process\System;

use Qlimix\Process\System\Exception\SystemException;

interface SystemInterface
{
    /**
     * @throws SystemException
     */
    public function kill(int $pid): void;

    /**
     * @throws SystemException
     */
    public function alive(int $pid): bool;

    /**
     * @throws SystemException
     */
    public function spawn(): int;

    /**
     * @throws SystemException
     */
    public function wait(): ?AwaitedProcess;
}
