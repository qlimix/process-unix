<?php declare(strict_types=1);

namespace Qlimix\Process\Runtime\Signal;

use Qlimix\Process\Runtime\RuntimeControlInterface;

final class RuntimeControlHandler implements HandlerInterface
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
    public function handle(): void
    {
        $this->runtimeControl->quit();
    }
}
