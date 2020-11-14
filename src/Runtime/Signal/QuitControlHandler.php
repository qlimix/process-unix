<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\Reason;
use Qlimix\Process\Runtime\RuntimeControlInterface;

final class QuitControlHandler implements HandlerInterface
{
    private RuntimeControlInterface $runtimeControl;

    public function __construct(RuntimeControlInterface $runtimeControl)
    {
        $this->runtimeControl = $runtimeControl;
    }

    /**
     * @inheritDoc
     */
    public function handle(int $signal): void
    {
        $this->runtimeControl->quit(new Reason('signal '.$signal));
    }
}
