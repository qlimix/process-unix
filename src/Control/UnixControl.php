<?php declare(strict_types=1);

namespace Qlimix\Process\Control;

use Qlimix\Process\Control\Exception\ControlException;
use Qlimix\Process\Runtime\Registry\RegistryInterface;
use Qlimix\Process\System\SystemInterface;
use Throwable;

final class UnixControl implements ControlInterface
{
    public function __construct(private SystemInterface $system, private RegistryInterface $registry)
    {
    }

    /**
     * @inheritDoc
     */
    public function status(): ?Status
    {
        try {
            $status = $this->system->status();
        } catch (Throwable $exception) {
            throw new ControlException('Failed to get status of a process', 0, $exception);
        }

        if ($status === null) {
            return null;
        }

        try {
            $process = $this->registry->remove($status->id);

            return new Status(
                $process->registryId,
                $process->process,
                $status->exitCode === 0
            );
        } catch (Throwable $exception) {
            throw new ControlException('Couldn\'t find pid in process list', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function start(string $process): int
    {
        try {
            $pid = $this->system->spawn($process);
        } catch (Throwable $exception) {
            throw new ControlException('Could not start new process', 0, $exception);
        }

        if ($pid > 0) {
            return $this->registry->add($pid, $process);
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function startMultiple(array $processes): array
    {
        $pids = [];
        foreach ($processes as $process) {
            $pids[] = $this->start($process);
        }

        return $pids;
    }

    /**
     * @inheritDoc
     */
    public function stop(int $id): void
    {
        try {
            $process = $this->registry->get($id);
        } catch (Throwable) {
            return;
        }

        try {
            $this->system->terminate($process->processId);
        } catch (Throwable $exception) {
            throw new ControlException('Failed to stop process', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function stopAll(): void
    {
        foreach ($this->registry->getAll() as $process) {
            try {
                $this->system->terminate($process->processId);
            } catch (Throwable) {
            }
        }
    }
}
