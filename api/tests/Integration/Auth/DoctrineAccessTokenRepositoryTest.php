<?php

declare(strict_types=1);

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Index\IndexedColumn;
use Doctrine\ORM\EntityManagerInterface;

it('persists an access token by its hash', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $hash = TokenHash::fromHash(hash('sha256', 'test-access-token'));
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $token = AccessToken::issue($userId, $hash, $createdAt->modify('+7 days'), $createdAt);

    $repository = self::getContainer()->get(AccessTokenRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($token);
    $entityManager->clear();

    $found = $repository->findByHash($hash);

    expect($found)->not->toBeNull()
        ->and($found->userId()->value())->toBe($userId->value())
        ->and($found->hash()->value())->toBe($hash->value())
        ->and($found->hash()->value())->not->toBe('test-access-token');
});

it('deletes expired tokens and keeps live ones', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $expiredHash = TokenHash::fromHash(hash('sha256', 'expired-access-token'));
    $liveHash = TokenHash::fromHash(hash('sha256', 'live-access-token'));
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $repository = self::getContainer()->get(AccessTokenRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(AccessToken::issue($userId, $expiredHash, $now->modify('-1 hour'), $now->modify('-2 hours')));
    $repository->save(AccessToken::issue($userId, $liveHash, $now->modify('+1 hour'), $now));
    $entityManager->clear();

    expect($repository->deleteExpired($now))->toBe(1);
    $entityManager->clear();

    expect($repository->findByHash($expiredHash))->toBeNull()
        ->and($repository->findByHash($liveHash))->not->toBeNull();
});

it('indexes access tokens by user id and expiry', function () {
    $indexes = self::getContainer()
        ->get(EntityManagerInterface::class)
        ->getConnection()
        ->createSchemaManager()
        ->listTableIndexes('access_tokens');

    $columnNames = static fn (Index $index): array => array_map(
        static fn (IndexedColumn $column): string => $column->getColumnName()->getIdentifier()->getValue(),
        $index->getIndexedColumns(),
    );

    expect($indexes)->toHaveKey('idx_access_tokens_user_id')
        ->and($indexes)->toHaveKey('idx_access_tokens_expires_at')
        ->and($columnNames($indexes['idx_access_tokens_user_id']))->toBe(['user_id'])
        ->and($columnNames($indexes['idx_access_tokens_expires_at']))->toBe(['expires_at']);
});

it('deletes an access token by its hash', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $hash = TokenHash::fromHash(hash('sha256', 'test-access-token'));
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $token = AccessToken::issue($userId, $hash, $createdAt->modify('+7 days'), $createdAt);

    $repository = self::getContainer()->get(AccessTokenRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($token);
    $entityManager->clear();

    $found = $repository->findByHash($hash);
    expect($found)->not->toBeNull();

    $repository->delete($found);
    $entityManager->clear();

    expect($repository->findByHash($hash))->toBeNull();
});
