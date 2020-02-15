<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Runtime;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Runtime\Reason;
use Qlimix\Process\Runtime\Signal\DispatcherInterface;
use Qlimix\Process\Runtime\Signal\Exception\DispatcherException;
use Qlimix\Process\Runtime\UnixRuntimeControl;

final class UnixRuntimeControlTest extends TestCase
{
    private MockObject $dispatcher;

    private MockObject $output;

    private UnixRuntimeControl $runtimeControl;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(DispatcherInterface::class);
        $this->output = $this->createMock(OutputInterface::class);

        $this->runtimeControl = new UnixRuntimeControl($this->dispatcher, $this->output);
    }

    public function testShouldTick(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');

        $this->runtimeControl->tick();
    }

    public function testShouldLogOnFailedDispatchTick(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new DispatcherException());

        $this->runtimeControl->tick();
    }

    public function testShouldAbort(): void
    {
        $this->runtimeControl->quit(new Reason('test'));
        $this->assertTrue($this->runtimeControl->abort());
    }

    public function testShouldNotAbort(): void
    {
        $this->assertFalse($this->runtimeControl->abort());
    }

    public function testShouldQuitAndAbort(): void
    {
        $this->runtimeControl->quit(new Reason('test'));
        $this->assertTrue($this->runtimeControl->abort());
    }
}
