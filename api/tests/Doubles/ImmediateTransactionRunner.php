<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Shared\Application\Transaction\TransactionRunner;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class ImmediateTransactionRunner implements TransactionRunner
{
    private int $depth = 0;

    /** @var list<object> */
    private array $pendingEvents = [];

    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function run(callable $work): mixed
    {
        $this->depth++;

        try {
            $result = $work();

            if ($this->depth === 1) {
                $this->dispatchPending();
            }

            return $result;
        } catch (Throwable $exception) {
            if ($this->depth === 1) {
                $this->pendingEvents = [];
            }

            throw $exception;
        } finally {
            $this->depth--;
        }
    }

    public function afterCommit(object $event): void
    {
        if ($this->depth === 0) {
            $this->eventDispatcher->dispatch($event);

            return;
        }

        $this->pendingEvents[] = $event;
    }

    private function dispatchPending(): void
    {
        $events = $this->pendingEvents;
        $this->pendingEvents = [];

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}
