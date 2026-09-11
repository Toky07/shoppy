<?php

declare(strict_types=1);

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;

it('issues a hashed token that expires', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $hash = TokenHash::fromHash(str_repeat('a', 64));
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $expiresAt = new DateTimeImmutable('2026-08-27T12:00:00+00:00');

    $token = AccessToken::issue($userId, $hash, $expiresAt, $createdAt);

    expect($token->userId())->toBe($userId)
        ->and($token->hash())->toBe($hash)
        ->and($token->expiresAt())->toBe($expiresAt)
        ->and($token->createdAt())->toBe($createdAt)
        ->and($token->isExpired($createdAt))->toBeFalse()
        ->and($token->isExpired($expiresAt))->toBeTrue();
});
