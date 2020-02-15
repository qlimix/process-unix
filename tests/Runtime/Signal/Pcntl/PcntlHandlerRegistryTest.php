<?php declare(strict_types=1);

namespace Qlimix\Tests\Process\Runtime\Signal\Pcntl;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Qlimix\Process\Runtime\Signal\HandlerInterface;
use Qlimix\Process\Runtime\Signal\Pcntl\PcntlHandlerRegistry;

final class PcntlHandlerRegistryTest extends TestCase
{
    private PcntlHandlerRegistry $handlerRegistry;

    private MockObject $handler;

    protected function setUp(): void
    {
        $this->handlerRegistry = new PcntlHandlerRegistry();

        $this->handler = $this->createMock(HandlerInterface::class);
    }

    public function testShouldRegister(): void
    {
        $this->handler->expects($this->once())
            ->method('handle');

        $this->handlerRegistry->register(1, $this->handler);
        $this->handlerRegistry->handle(1, []);
    }

    public function testShouldNotReregisterAlreadyRegisteredSignal(): void
    {
        $this->handler->expects($this->exactly(2))
            ->method('handle');

        $this->handlerRegistry->register(1, $this->handler);
        $this->handlerRegistry->register(1, $this->handler);
        $this->handlerRegistry->handle(1, []);
    }

    public function testShouldReturnOnNoHandlersFound(): void
    {
        $this->handlerRegistry->handle(1, []);
        $this->addToAssertionCount(1);
    }
}
