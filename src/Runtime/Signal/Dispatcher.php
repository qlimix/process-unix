<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\Signal\Exception\SignalException;

interface Dispatcher
{
    /**
     * @throws SignalException
     */
    public function dispatch(): void;
}
