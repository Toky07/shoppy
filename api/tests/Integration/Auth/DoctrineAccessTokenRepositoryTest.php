<?php

declare(strict_types=1);

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
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
