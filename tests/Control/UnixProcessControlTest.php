<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Control;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Control\Exception\ControlException;
use Qlimix\Process\Control\UnixControl;
use Qlimix\Process\Runtime\Registry\Exception\NotFoundException;
use Qlimix\Process\Runtime\Registry\Process;
use Qlimix\Process\Runtime\Registry\RegistryInterface;
use Qlimix\Process\System\Exception\SystemException;
use Qlimix\Process\System\Status;
use Qlimix\Process\System\SystemInterface;

final class UnixProcessControlTest extends TestCase
{
    private const PROCESS = 'bin/test';

    private MockObject $system;

    private MockObject $registry;

    private UnixControl $control;

    public function setUp(): void
    {
        $this->system = $this->createMock(SystemInterface::class);
        $this->registry = $this->createMock(RegistryInterface::class);

        $this->control = new UnixControl(
            $this->system,
            $this->registry
        );
    }

    public function testShouldReturnStatus(): void
    {
        $process = new Process(1, 2, self::PROCESS);

        $this->system->expects($this->once())
            ->method('status')
            ->willReturn(new Status(1, 0));

        $this->registry->expects($this->once())
            ->method('remove')
            ->willReturn($process);

        $status = $this->control->status();
        $this->assertSame(1, $status->getId());
        $this->assertSame(self::PROCESS, $status->getProcess());
        $this->assertTrue($status->isSuccess());
    }

    public function testShouldReturnNull(): void
    {
        $this->system->expects($this->once())
            ->method('status')
            ->willReturn(null);

        $this->registry->expects($this->never())
            ->method('remove');

        $this->assertNull($this->control->status());
    }

    public function testShouldThrowOnStatusException(): void
    {
        $this->system->expects($this->once())
            ->method('status')
            ->willThrowException(new SystemException());

        $this->registry->expects($this->never())
            ->method('remove');

        $this->expectException(ControlException::class);
        $this->control->status();
    }

    public function testShouldThrowOnRegistryRemoveException(): void
    {
        $this->system->expects($this->once())
            ->method('status')
            ->willReturn(new Status(1, 0));

        $this->registry->expects($this->once())
            ->method('remove')
            ->willThrowException(new NotFoundException());

        $this->expectException(ControlException::class);
        $this->control->status();
    }

    public function testShouldStartProcess(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(5);

        $this->registry->expects($this->once())
            ->method('add')
            ->willReturn(1);

        $pid = $this->control->start(self::PROCESS);

        $this->assertSame($pid, 1);
    }

    public function testShouldRunSpawnedProcess(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(0);

        $this->control->start(self::PROCESS);
    }

    public function testShouldRunSpawnedProcessButFail(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(0);

        $this->control->start(self::PROCESS);
    }

    public function testShouldStartProcesses(): void
    {
        $this->system->expects($this->exactly(3))
            ->method('spawn')
            ->willReturnOnConsecutiveCalls(1,2,3);

        $processes = [
            self::PROCESS,
            self::PROCESS,
            self::PROCESS,
        ];

        $this->control->startMultiple($processes);
    }

    public function testShouldThrowOnStartProcessesFailed(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willThrowException(new SystemException());

        $this->expectException(ControlException::class);

        $this->control->startMultiple([self::PROCESS]);
    }

    public function testShouldStopProcess(): void
    {
        $this->registry->expects($this->once())
            ->method('get')
            ->willReturn(new Process(1, 2, self::PROCESS));

        $this->system->expects($this->once())
            ->method('terminate');

        $this->control->stop(1);
    }

    public function testShouldReturnOnUnknownPid(): void
    {
        $this->registry->expects($this->once())
            ->method('get')
            ->willThrowException(new NotFoundException());

        $this->system->expects($this->never())
            ->method('terminate');

        $this->control->stop(1);
    }

    public function testShouldThrowOnFailedTerminate(): void
    {
        $this->registry->expects($this->once())
            ->method('get')
            ->willReturn(new Process(1, 2, self::PROCESS));

        $this->system->expects($this->once())
            ->method('terminate')
            ->willThrowException(new SystemException());

        $this->expectException(ControlException::class);

        $this->control->stop(1);
    }

    public function testShouldStopProcesses(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([
                new Process(1, 1, self::PROCESS),
                new Process(2, 2, self::PROCESS),
                new Process(3, 3, self::PROCESS),
            ]);

        $this->system->expects($this->exactly(3))
            ->method('terminate');

        $this->control->stopAll();
    }

    public function testShouldNotThrowOnExceptionStopProcesses(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([
                new Process(1, 1, self::PROCESS),
                new Process(2, 2, self::PROCESS),
                new Process(3, 3, self::PROCESS),
            ]);

        $this->system->expects($this->exactly(3))
            ->method('terminate')
            ->willThrowException(new SystemException());

        $this->control->stopAll();
    }
}
