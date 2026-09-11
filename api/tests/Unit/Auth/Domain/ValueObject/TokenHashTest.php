<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\InvalidTokenHash;
use App\Auth\Domain\ValueObject\TokenHash;

it('exposes the hash it was created with', function () {
    $hash = TokenHash::fromHash(str_repeat('a', 64));

    expect($hash->value())->toBe(str_repeat('a', 64));
});

it('rejects an empty hash', function () {
    TokenHash::fromHash('');
})->throws(InvalidTokenHash::class);
