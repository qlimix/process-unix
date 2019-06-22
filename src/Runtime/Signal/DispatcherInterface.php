<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\Signal\Exception\DispatcherException;

interface DispatcherInterface
{
    /**
     * @throws DispatcherException
     */
    public function dispatch(): void;
}
