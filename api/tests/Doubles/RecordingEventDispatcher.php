<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RecordingEventDispatcher implements EventDispatcherInterface
{
    /** @var list<object> */
    public array $dispatched = [];

    public function dispatch(object $event, ?string $eventName = null): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
    }

    public function removeListener(string $eventName, callable $listener): void
    {
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
    }

    public function getListeners(?string $eventName = null): array
    {
        return [];
    }

    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        return null;
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return false;
    }
}
