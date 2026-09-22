<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Domain\Event\EventBus;

final class RecordingEventBus implements EventBus
{
    /** @var list<DomainEvent> */
    public array $published = [];

    public function publish(DomainEvent $event): void
    {
        $this->published[] = $event;
    }
}
