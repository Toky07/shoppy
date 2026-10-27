<?php

declare(strict_types=1);

use App\Audit\Application\Query\ListAuditLogQuery;
use App\Audit\Application\QueryHandler\ListAuditLogQueryHandler;
use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\Exception\InvalidAuditPagination;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditId;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use App\Audit\Infrastructure\Persistence\InMemoryAuditLogRepository;

function saveAuditEntry(
    InMemoryAuditLogRepository $log,
    string $id,
    string $action,
    DateTimeImmutable $createdAt,
    string $subjectId = '11111111-1111-4111-8111-111111111111',
): void {
    $log->save(AuditEntry::record(
        AuditId::fromString($id),
        AuditAction::fromString($action),
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
        'admin@nuvora.test',
        AuditSubjectType::user(),
        $subjectId,
        ['from' => 'customer', 'to' => 'admin'],
        $createdAt,
    ));
}

it('lists audit entries newest first', function () {
    $log = new InMemoryAuditLogRepository();
    saveAuditEntry($log, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'role_changed', new DateTimeImmutable('2026-10-26T12:00:00+00:00'));
    saveAuditEntry($log, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'price_changed', new DateTimeImmutable('2026-10-27T12:00:00+00:00'));

    $response = (new ListAuditLogQueryHandler($log))->handle(new ListAuditLogQuery());

    expect($response->toArray())->toMatchArray([
        'page' => 1,
        'limit' => 20,
        'total' => 2,
    ])
        ->and($response->items[0]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($response->items[0]->action)->toBe('price_changed')
        ->and($response->items[0]->actorEmail)->toBe('admin@nuvora.test')
        ->and($response->items[1]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('paginates the audit log', function () {
    $log = new InMemoryAuditLogRepository();
    saveAuditEntry($log, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', 'role_changed', new DateTimeImmutable('2026-10-25T12:00:00+00:00'));
    saveAuditEntry($log, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', 'stock_changed', new DateTimeImmutable('2026-10-26T12:00:00+00:00'));
    saveAuditEntry($log, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', 'payment_changed', new DateTimeImmutable('2026-10-27T12:00:00+00:00'));

    $response = (new ListAuditLogQueryHandler($log))->handle(new ListAuditLogQuery(page: 2, limit: 2));

    expect($response->total)->toBe(3)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('rejects an invalid audit page', function () {
    (new ListAuditLogQueryHandler(new InMemoryAuditLogRepository()))->handle(new ListAuditLogQuery(page: 0));
})->throws(InvalidAuditPagination::class);
