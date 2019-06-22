<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Runtime\Signal;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\Runtime\Signal\RuntimeControlHandler;

final class RuntimeControlHandlerTest extends TestCase
{
    /** @var MockObject */
    private $runtimeControl;

    /** @var RuntimeControlHandler */
    private $runtimeHandler;

    protected function setUp(): void
    {
        $this->runtimeControl = $this->createMock(RuntimeControlInterface::class);
        $this->runtimeHandler = new RuntimeControlHandler($this->runtimeControl);
    }

    /**
     * @test
     */
    public function shouldHandle(): void
    {
        $this->runtimeControl->expects($this->once())
            ->method('quit');

        $this->runtimeHandler->handle(1, 'foo');
    }
}
