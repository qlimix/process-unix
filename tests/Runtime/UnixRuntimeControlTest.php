<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Runtime;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Output\OutputInterface;
use Qlimix\Process\Runtime\Signal\DispatcherInterface;
use Qlimix\Process\Runtime\UnixRuntimeControl;

final class UnixRuntimeControlTest extends TestCase
{
    /** @var MockObject */
    private $dispatcher;

    /** @var MockObject */
    private $output;

    /** @var UnixRuntimeControl */
    private $runtimeControl;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(DispatcherInterface::class);
        $this->output = $this->createMock(OutputInterface::class);

        $this->runtimeControl = new UnixRuntimeControl(
            $this->dispatcher,
            $this->output
        );
    }

    /**
     * @test
     */
    public function shouldTick(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch');

        $this->runtimeControl->tick();
    }

    /**
     * @test
     */
    public function shouldAbort(): void
    {
        $this->runtimeControl->quit();
        $this->assertTrue($this->runtimeControl->abort());
    }

    /**
     * @test
     */
    public function shouldNotAbort(): void
    {
        $this->assertFalse($this->runtimeControl->abort());
    }

    /**
     * @test
     */
    public function shouldQuitAndAbort(): void
    {
        $this->runtimeControl->quit();
        $this->assertTrue($this->runtimeControl->abort());
    }
}
