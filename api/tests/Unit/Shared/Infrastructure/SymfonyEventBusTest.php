<?php

declare(strict_types=1);

use App\Shared\Domain\Event\DomainEvent;
use App\Shared\Infrastructure\Event\SymfonyEventBus;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('publishes a domain event to listeners of its class', function () {
    $event = new class implements DomainEvent {};
    $dispatcher = new EventDispatcher();
    $received = null;
    $dispatcher->addListener($event::class, function (object $dispatched) use (&$received): void {
        $received = $dispatched;
    });

    (new SymfonyEventBus($dispatcher))->publish($event);

    expect($received)->toBe($event);
});
