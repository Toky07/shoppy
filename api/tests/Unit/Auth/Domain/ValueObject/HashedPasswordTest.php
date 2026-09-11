<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\InvalidHashedPassword;
use App\Auth\Domain\ValueObject\HashedPassword;

it('exposes the hash it was created with', function () {
    $hash = HashedPassword::fromHash('$argon2id$example');

    expect($hash->value())->toBe('$argon2id$example');
});

it('rejects an empty hash', function () {
    HashedPassword::fromHash('');
})->throws(InvalidHashedPassword::class);
