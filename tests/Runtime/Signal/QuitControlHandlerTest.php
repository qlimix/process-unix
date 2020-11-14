<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Runtime\Signal;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\Runtime\Signal\QuitControlHandler;

final class QuitControlHandlerTest extends TestCase
{
    private MockObject $runtimeControl;

    private QuitControlHandler $runtimeHandler;

    protected function setUp(): void
    {
        $this->runtimeControl = $this->createMock(RuntimeControlInterface::class);
        $this->runtimeHandler = new QuitControlHandler($this->runtimeControl);
    }

    public function testShouldHandle(): void
    {
        $this->runtimeControl->expects($this->once())
            ->method('quit');

        $this->runtimeHandler->handle(1, 'foo');
    }
}
