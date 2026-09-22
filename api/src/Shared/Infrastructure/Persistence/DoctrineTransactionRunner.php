<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\Transaction\TransactionRunner;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

final class DoctrineTransactionRunner implements TransactionRunner
{
    private int $depth = 0;

    /** @var list<object> */
    private array $pendingEvents = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function run(callable $work): mixed
    {
        $this->depth++;
        $connection = $this->entityManager->getConnection();
        $startedHere = false;

        try {
            if ($this->depth === 1 && !$connection->isTransactionActive()) {
                $this->begin($connection);
                $startedHere = true;
            }

            try {
                $result = $work();

                if ($startedHere) {
                    $this->entityManager->flush();
                    $this->commit($connection);
                    $this->dispatchPending();
                }

                return $result;
            } catch (Throwable $exception) {
                if ($startedHere) {
                    $this->rollback($connection);
                    $this->pendingEvents = [];
                    $this->entityManager->clear();
                }

                throw $exception;
            }
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

    private function begin(Connection $connection): void
    {
        $connection->beginTransaction();
    }

    private function commit(Connection $connection): void
    {
        $connection->commit();
    }

    private function rollback(Connection $connection): void
    {
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
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
