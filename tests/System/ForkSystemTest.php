<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\System;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\System\Exception\SystemException;
use Qlimix\Process\System\ForkSystem;
use Qlimix\Process\Terminate\TerminationInterface;
use function usleep;

final class ForkSystemTest extends TestCase
{
    private const NONE_EXISTING_PID = 999999999;

    private MockObject $termination;

    private ForkSystem $system;

    private string $process;

    protected function setUp(): void
    {
        $this->termination = $this->createMock(TerminationInterface::class);

        $this->system = new ForkSystem($this->termination);

        $this->process = getcwd().'/../tests/sleep';
    }

    public function testShouldStatus(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(10000);
        $this->system->terminate($pid);

        usleep(10000);

        $status = $this->system->status();

        $this->assertIsObject($status);
        $this->assertSame($status->getExitCode(), 0);
    }

    public function testShouldNull(): void
    {
        $pid = $this->system->spawn($this->process);
        $this->assertNull($this->system->status());
        $this->system->terminate($pid);
        usleep(10000);
        $this->system->status();
    }

    public function testShouldThrowOnMinusPid(): void
    {
        $this->expectException(SystemException::class);
        $this->system->status();
    }

    public function testShouldKill(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(10000);
        $this->system->kill($pid);

        usleep(10000);

        $status = $this->system->status();

        $this->assertIsObject($status);
        $this->assertSame($status->getExitCode(), 0);
    }

    public function testShouldThrowOnFailedKill(): void
    {
        $this->expectException(SystemException::class);
        $this->system->kill(self::NONE_EXISTING_PID);
    }

    public function testShouldThrowOnFailedTerminate(): void
    {
        $this->expectException(SystemException::class);
        $this->system->terminate(self::NONE_EXISTING_PID);
    }

    public function testShouldThrowOnFailedAlive(): void
    {
        $this->expectException(SystemException::class);
        $this->system->terminate(self::NONE_EXISTING_PID);
    }

    public function testShouldCheckAlive(): void
    {
        $pid = $this->system->spawn($this->process);
        usleep(10000);
        $alive = $this->system->alive($pid);
        $this->system->kill($pid);

        usleep(10000);

        $status = $this->system->status();

        $this->assertIsObject($status);
        $this->assertSame($status->getExitCode(), 0);
        $this->assertTrue($alive);
    }
}
