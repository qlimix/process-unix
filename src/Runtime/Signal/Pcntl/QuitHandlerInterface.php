<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal\Pcntl;

use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\Runtime\Signal\HandlerInterface;

final class QuitHandlerInterface implements HandlerInterface
{
    /** @var RuntimeControlInterface */
    private $runtimeControl;

    public function __construct(RuntimeControlInterface $runtimeControl)
    {
        $this->runtimeControl = $runtimeControl;
    }

    /**
     * @inheritDoc
     */
    public function handle(int $signo, $signinfo): void
    {
        $this->runtimeControl->quit();
    }
}
