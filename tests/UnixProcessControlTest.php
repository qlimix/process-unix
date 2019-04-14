<?php declare(strict_types=1);

namespace Qlimix\Tests\Process;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Exception\ProcessException;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\ProcessInterface;
use Qlimix\Process\Registry\Exception\NotFoundException;
use Qlimix\Process\Registry\RegisteredProcess;
use Qlimix\Process\Registry\RegistryInterface;
use Qlimix\Process\Runtime\RuntimeControlInterface;
use Qlimix\Process\System\AwaitedProcess;
use Qlimix\Process\System\Exception\SystemException;
use Qlimix\Process\System\SystemInterface;
use Qlimix\Process\Terminate\TerminationInterface;
use Qlimix\Process\UnixProcessControl;

final class UnixProcessControlTest extends TestCase
{
    /** @var MockObject */
    private $runtimeControl;

    /** @var MockObject */
    private $system;

    /** @var MockObject */
    private $output;

    /** @var MockObject */
    private $registry;

    /** @var MockObject */
    private $termination;

    /** @var UnixProcessControl */
    private $processControl;

    public function setUp(): void
    {
        $this->runtimeControl = $this->createMock(RuntimeControlInterface::class);
        $this->system = $this->createMock(SystemInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->termination = $this->createMock(TerminationInterface::class);

        $this->processControl = new UnixProcessControl(
            $this->runtimeControl,
            $this->system,
            $this->output,
            $this->registry,
            $this->termination
        );
    }

    /**
     * @test
     */
    public function shouldCheckStatus(): void
    {
        $this->system->expects($this->once())
            ->method('wait')
            ->willReturn(new AwaitedProcess(5, 0));

        $this->registry->expects($this->once())
            ->method('remove')
            ->willReturn(1);

        $status = $this->processControl->status();

        $this->assertSame($status->getPid(), 1);
        $this->assertTrue($status->success());
    }

    /**
     * @test
     */
    public function shouldReturnOnNullCheckStatus(): void
    {
        $this->system->expects($this->once())
            ->method('wait')
            ->willReturn(null);

        $status = $this->processControl->status();

        $this->assertNull($status);
    }

    /**
     * @test
     */
    public function shouldThrowOnStatusCheckException(): void
    {
        $this->system->expects($this->once())
            ->method('wait')
            ->willThrowException(new SystemException());

        $this->expectException(ProcessException::class);

        $this->processControl->status();
    }

    /**
     * @test
     */
    public function shouldThrowOnInvalidPid(): void
    {
        $this->system->expects($this->once())
            ->method('wait')
            ->willReturn(new AwaitedProcess(5, 0));

        $this->registry->expects($this->once())
            ->method('remove')
            ->willThrowException(new NotFoundException());

        $this->expectException(ProcessException::class);

        $this->processControl->status();
    }

    /**
     * @test
     */
    public function shouldHaveRunningProcess(): void
    {
        $this->registry->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $this->system->expects($this->once())
            ->method('alive')
            ->willReturn(true);

        $this->assertTrue($this->processControl->isProcessRunning(1));
    }

    /**
     * @test
     */
    public function shouldNotHaveRunningProcess(): void
    {
        $this->registry->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $pid = $this->processControl->startProcess($this->createMock(ProcessInterface::class));

        $this->system->expects($this->once())
            ->method('alive')
            ->willReturn(false);

        $this->assertFalse($this->processControl->isProcessRunning($pid));
    }

    /**
     * @test
     */
    public function shouldNotHavePidForRunningProcess(): void
    {
        $this->system->expects($this->never())
            ->method('alive');

        $this->assertFalse($this->processControl->isProcessRunning(1));
    }

    /**
     * @test
     */
    public function shouldStartProcess(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(5);

        $this->registry->expects($this->once())
            ->method('add')
            ->willReturn(1);

        $pid = $this->processControl->startProcess($this->createMock(ProcessInterface::class));

        $this->assertSame($pid, 1);
    }

    /**
     * @test
     */
    public function shouldRunSpawnedProcess(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(0);

        $process = $this->createMock(ProcessInterface::class);

        $process->expects($this->once())
            ->method('run');

        $this->termination->expects($this->once())
            ->method('success');

        $this->processControl->startProcess($process);
    }

    /**
     * @test
     */
    public function shouldRunSpawnedProcessButFail(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willReturn(0);

        $process = $this->createMock(ProcessInterface::class);

        $process->expects($this->once())
            ->method('run')
            ->willThrowException(new Exception());

        $this->termination->expects($this->once())
            ->method('fail');

        $this->processControl->startProcess($process);
    }

    /**
     * @test
     */
    public function shouldStartProcesses(): void
    {
        $this->system->expects($this->exactly(3))
            ->method('spawn')
            ->willReturnOnConsecutiveCalls(1,2,3);

        $processes = [
            $this->createMock(ProcessInterface::class),
            $this->createMock(ProcessInterface::class),
            $this->createMock(ProcessInterface::class),
        ];

        $this->processControl->startProcesses($processes);
    }

    /**
     * @test
     */
    public function shouldThrowOnStartProcessesFailed(): void
    {
        $this->system->expects($this->once())
            ->method('spawn')
            ->willThrowException(new SystemException());

        $processes = [
            $this->createMock(ProcessInterface::class),
        ];

        $this->expectException(ProcessException::class);

        $this->processControl->startProcesses($processes);
    }

    /**
     * @test
     */
    public function shouldStopProcess(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([
                new RegisteredProcess(1, 11, $this->createMock(ProcessInterface::class)),
                new RegisteredProcess(2, 22, $this->createMock(ProcessInterface::class)),
                new RegisteredProcess(3, 33, $this->createMock(ProcessInterface::class)),
            ]);

        $this->system->expects($this->exactly(3))
            ->method('terminate');

        $this->processControl->stopProcesses();
    }

    /**
     * @test
     */
    public function shouldNotThrowOnExceptionStopProcess(): void
    {
        $this->registry->expects($this->once())
            ->method('getAll')
            ->willReturn([
                new RegisteredProcess(1, 11, $this->createMock(ProcessInterface::class)),
                new RegisteredProcess(2, 22, $this->createMock(ProcessInterface::class)),
                new RegisteredProcess(3, 33, $this->createMock(ProcessInterface::class)),
            ]);

        $this->system->expects($this->exactly(3))
            ->method('terminate')
            ->willThrowException(new SystemException());

        $this->processControl->stopProcesses();
    }
}
