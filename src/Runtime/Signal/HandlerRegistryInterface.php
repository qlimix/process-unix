<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\Signal\Exception\DispatcherException;

interface HandlerRegistryInterface
{
    /**
     * @throws DispatcherException
     */
    public function register(int $signal, HandlerInterface $handler): void;
}
