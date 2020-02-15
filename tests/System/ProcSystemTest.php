<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\System;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\System\Exception\SystemException;
use Qlimix\Process\System\ProcSystem;
use Qlimix\Process\Terminate\TerminationInterface;
use function pcntl_waitpid;
use function posix_kill;
use function usleep;
use const SIGKILL;

final class ProcSystemTest extends TestCase
{
    private const NONE_EXISTING_PID = 99999999;

    private MockObject $termination;

    private ProcSystem $system;

    private string $process;

    protected function setUp(): void
    {
        $this->termination = $this->createMock(TerminationInterface::class);

        $this->system = new ProcSystem();

        $this->process = dirname(__DIR__).'/sleep';
    }

    public function testShouldStatusReturnStatus(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(1000);

        $this->system->kill($pid);
        usleep(1000);

        $status = $this->system->status();

        $this->assertIsObject($status);
    }

    public function testShouldReturnNullNoProcessToGetStatusFrom(): void
    {
        $this->assertNull($this->system->status());
    }

    public function testShouldKillProcess(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(1000);

        $this->system->kill($pid);
        usleep(1000);

        $status = $this->system->status();
        $this->assertSame($pid, $status->getId());
    }

    public function testShouldThrowOnNoneExistingPidKill(): void
    {
        $this->expectException(SystemException::class);
        $this->system->kill(99999999);
    }

    public function testShouldThrowOnKillOnOutdatedProc(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(1000);

        posix_kill($pid, SIGKILL);
        pcntl_waitpid($pid, $status);
        $this->expectException(SystemException::class);
        $this->system->kill($pid);
        usleep(1000);

        $this->system->status();
    }

    public function testShouldTerminateProcess(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(100000);

        $this->system->terminate($pid);
        usleep(100000);

        $status = $this->system->status();
        $this->assertSame($pid, $status->getId());
        $this->assertSame(0, $status->getExitCode());
    }

    public function testShouldThrowOnNoneExistingPidTerminate(): void
    {
        $this->expectException(SystemException::class);
        $this->system->terminate(self::NONE_EXISTING_PID);
    }

    public function testShouldThrowOnTerminateOnOutdatedProc(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(1000);

        posix_kill($pid, SIGKILL);
        pcntl_waitpid($pid, $status);
        $this->expectException(SystemException::class);
        $this->system->terminate($pid);

        usleep(1000);
        $this->system->status();
    }

    public function testShouldBeAlive(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(1000);

        $this->assertTrue($this->system->alive($pid));
        $this->system->terminate($pid);

        usleep(1000);
        $this->system->status();
    }

    public function testShouldNotBeAlive(): void
    {
        $this->assertFalse($this->system->alive(self::NONE_EXISTING_PID));
    }
}
