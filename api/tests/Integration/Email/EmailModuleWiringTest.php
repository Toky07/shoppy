<?php

declare(strict_types=1);

use App\Email\Application\Port\Mailer;
use App\Shared\Domain\Event\EventBus;
use App\Shared\Infrastructure\Event\SymfonyEventBus;
use App\Tests\Doubles\InMemoryMailer;

it('wires the event bus and the mailer port', function () {
    expect($this->getContainer()->get(EventBus::class))->toBeInstanceOf(SymfonyEventBus::class)
        ->and($this->getContainer()->get(Mailer::class))->toBeInstanceOf(InMemoryMailer::class);
});
