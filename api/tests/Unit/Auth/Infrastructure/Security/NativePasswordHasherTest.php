<?php

declare(strict_types=1);

use App\Auth\Domain\ValueObject\PlainPassword;
use App\Auth\Infrastructure\Security\NativePasswordHasher;

it('hashes a password with argon2id', function () {
    $hasher = new NativePasswordHasher();
    $plain = PlainPassword::fromString('secret-secret');

    $hash = $hasher->hash($plain);

    expect($hash->value())->not->toBe('secret-secret')
        ->and($hash->value())->toStartWith('$argon2id$')
        ->and(password_verify('secret-secret', $hash->value()))->toBeTrue()
        ->and(password_verify('wrong-password', $hash->value()))->toBeFalse();
});

it('verifies a password against its hash', function () {
    $hasher = new NativePasswordHasher();
    $plain = PlainPassword::fromString('secret-secret');
    $hash = $hasher->hash($plain);

    expect($hasher->verify($hash, $plain))->toBeTrue()
        ->and($hasher->verify($hash, PlainPassword::fromString('wrong-password')))->toBeFalse();
});
