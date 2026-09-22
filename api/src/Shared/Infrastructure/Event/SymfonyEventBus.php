<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Event;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Event\EventBus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class SymfonyEventBus implements EventBus
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function publish(DomainEvent $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }
}
