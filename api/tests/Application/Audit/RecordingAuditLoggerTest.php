<?php

declare(strict_types=1);

use App\Audit\Application\RecordingAuditLogger;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Infrastructure\Persistence\InMemoryAuditLogRepository;
use App\Tests\Doubles\FakeCurrentActor;
use App\Tests\Doubles\FixedClock;

it('records the actor, subject and metadata', function () {
    $log = new InMemoryAuditLogRepository();
    $now = new DateTimeImmutable('2026-10-27T12:00:00+00:00');
    $logger = new RecordingAuditLogger(
        $log,
        new FakeCurrentActor('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9', 'admin@nuvora.test'),
        new FixedClock($now),
    );

    $logger->record(AuditAction::ROLE_CHANGED, 'user', '11111111-1111-4111-8111-111111111111', [
        'from' => 'customer',
        'to' => 'admin',
    ]);

    $entry = $log->findPage(0, 1)[0];

    expect($entry->action()->value())->toBe('role_changed')
        ->and($entry->actorId())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9')
        ->and($entry->actorEmail())->toBe('admin@nuvora.test')
        ->and($entry->subjectType()->value())->toBe('user')
        ->and($entry->subjectId())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($entry->metadata())->toEqual(['from' => 'customer', 'to' => 'admin'])
        ->and($entry->createdAt())->toEqual($now);
});

it('records a system actor when nobody is signed in', function () {
    $log = new InMemoryAuditLogRepository();
    $logger = new RecordingAuditLogger(
        $log,
        new FakeCurrentActor(null, null),
        new FixedClock(new DateTimeImmutable('2026-10-27T12:00:00+00:00')),
    );

    $logger->record(AuditAction::PAYMENT_CHANGED, 'order', 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', [
        'from' => 'pending',
        'to' => 'paid',
    ]);

    $entry = $log->findPage(0, 1)[0];

    expect($entry->actorId())->toBeNull()
        ->and($entry->actorEmail())->toBeNull()
        ->and($entry->action()->value())->toBe('payment_changed');
});
