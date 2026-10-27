<?php

declare(strict_types=1);

use App\Email\Domain\Event\EmailRequested;
use App\Newsletter\Application\Command\SubscribeNewsletterCommand;
use App\Newsletter\Application\CommandHandler\SubscribeNewsletterCommandHandler;
use App\Newsletter\Domain\Exception\InvalidNewsletterEmail;
use App\Newsletter\Infrastructure\Persistence\InMemoryNewsletterSubscriberRepository;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\RecordingEventBus;

function newsletterSubscription(): array
{
    $subscribers = new InMemoryNewsletterSubscriberRepository();
    $events = new RecordingEventBus();
    $clock = new FixedClock(new DateTimeImmutable('2026-10-27T12:00:00+00:00'));
    $handler = new SubscribeNewsletterCommandHandler($subscribers, $events, $clock);

    return [$handler, $subscribers, $events];
}

it('stores a new subscriber and sends a confirmation', function () {
    [$handler, $subscribers, $events] = newsletterSubscription();

    $handler->handle(new SubscribeNewsletterCommand('Ada@Shoppy.test'));

    $saved = $subscribers->findByEmail(\App\Email\Domain\ValueObject\EmailAddress::fromString('ada@shoppy.test'));

    expect($saved)->not->toBeNull()
        ->and($saved->email()->value())->toBe('ada@shoppy.test')
        ->and($saved->subscribedAt()->format(DateTimeInterface::ATOM))->toBe('2026-10-27T12:00:00+00:00')
        ->and($events->published)->toHaveCount(1)
        ->and($events->published[0])->toBeInstanceOf(EmailRequested::class)
        ->and($events->published[0]->to->value())->toBe('ada@shoppy.test')
        ->and($events->published[0]->subject->value())->toBe('Inscription à la lettre Shoppy')
        ->and($events->published[0]->body->text())->toContain('inscrit');
});

it('is idempotent for an address already on the list', function () {
    [$handler, $subscribers, $events] = newsletterSubscription();

    $handler->handle(new SubscribeNewsletterCommand('ada@shoppy.test'));
    $handler->handle(new SubscribeNewsletterCommand('ADA@shoppy.test'));

    expect($subscribers->count())->toBe(1)
        ->and($events->published)->toHaveCount(1);
});

it('rejects an invalid email', function () {
    [$handler] = newsletterSubscription();

    $handler->handle(new SubscribeNewsletterCommand('not-an-email'));
})->throws(InvalidNewsletterEmail::class);
