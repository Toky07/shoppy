<?php

declare(strict_types=1);

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\UserId;

it('stores a hashed password for a user', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $hashedPassword = HashedPassword::fromHash('$argon2id$example');

    $credentials = Credentials::create($userId, $hashedPassword);

    expect($credentials->userId())->toBe($userId)
        ->and($credentials->hashedPassword())->toBe($hashedPassword)
        ->and($credentials->hashedPassword()->value())->not->toBe('secret-secret');
});
