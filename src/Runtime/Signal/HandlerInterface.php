<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\Signal\Exception\SignalException;

interface HandlerInterface
{
    /**
     * @param mixed $signinfo
     *
     * @throws SignalException
     */
    public function handle(int $signo, $signinfo): void;
}