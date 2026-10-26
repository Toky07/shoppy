<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Domain\ValueObject\PlainPassword;

it('accepts a password of twelve characters', function () {
    $password = PlainPassword::fromString('secret-secret');

    expect($password->value())->toBe('secret-secret');
});

it('keeps surrounding whitespace in the password', function () {
    $password = PlainPassword::fromString(' secret-secret');

    expect($password->value())->toBe(' secret-secret');
});

it('rejects a password of eleven characters', function () {
    PlainPassword::fromString('12345678901');
})->throws(InvalidPassword::class);

it('rejects a password that is too short', function () {
    PlainPassword::fromString('short');
})->throws(InvalidPassword::class);

it('accepts a twenty-one character password', function () {
    $password = str_repeat('a', 21);

    expect(PlainPassword::fromString($password)->value())->toBe($password);
});

it('accepts a password of 4096 characters', function () {
    $password = str_repeat('a', 4096);

    expect(PlainPassword::fromString($password)->value())->toBe($password);
});

it('rejects a password longer than 4096 characters', function () {
    PlainPassword::fromString(str_repeat('a', 4097));
})->throws(InvalidPassword::class);

it('allows a short password only for verification', function () {
    expect(PlainPassword::forVerification('shortish')->value())->toBe('shortish');
});

it('rejects an empty password for verification', function () {
    PlainPassword::forVerification('');
})->throws(InvalidPassword::class);

it('describes the same bounds as the value object', function () {
    expect((new InvalidPassword())->getMessage())->toBe(sprintf(
        'Password must be between %d and %d characters.',
        PlainPassword::MIN_LENGTH,
        PlainPassword::MAX_LENGTH,
    ))
        ->and(PlainPassword::MIN_LENGTH)->toBe(12)
        ->and(PlainPassword::MAX_LENGTH)->toBe(4096);
});
