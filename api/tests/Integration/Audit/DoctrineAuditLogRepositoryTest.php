<?php

declare(strict_types=1);

use App\Audit\Domain\Entity\AuditEntry;
use App\Audit\Domain\Repository\AuditLogRepository;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditId;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use Doctrine\ORM\EntityManagerInterface;

it('persists audit entries newest first', function () {
    $repository = self::getContainer()->get(AuditLogRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $older = AuditEntry::record(
        AuditId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        AuditAction::roleChanged(),
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
        'admin@nuvora.test',
        AuditSubjectType::user(),
        '11111111-1111-4111-8111-111111111111',
        ['from' => 'customer', 'to' => 'admin'],
        new DateTimeImmutable('2026-10-26T12:00:00+00:00'),
    );
    $newer = AuditEntry::record(
        AuditId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2'),
        AuditAction::priceChanged(),
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
        'admin@nuvora.test',
        AuditSubjectType::product(),
        '550e8400-e29b-41d4-a716-446655440000',
        ['fromCents' => 1999, 'toCents' => 2499],
        new DateTimeImmutable('2026-10-27T12:00:00+00:00'),
    );

    $repository->save($older);
    $repository->save($newer);
    $entityManager->clear();

    $page = $repository->findPage(0, 20);

    expect($repository->countAll())->toBe(2)
        ->and($page)->toHaveCount(2)
        ->and($page[0]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($page[0]->action()->value())->toBe('price_changed')
        ->and($page[0]->metadata())->toEqual(['fromCents' => 1999, 'toCents' => 2499])
        ->and($page[1]->actorEmail())->toBe('admin@nuvora.test');
});
