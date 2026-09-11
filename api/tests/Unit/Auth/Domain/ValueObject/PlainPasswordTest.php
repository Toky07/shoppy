<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Domain\ValueObject\PlainPassword;

it('accepts a password of eight characters', function () {
    $password = PlainPassword::fromString('secret12');

    expect($password->value())->toBe('secret12');
});

it('keeps surrounding whitespace in the password', function () {
    $password = PlainPassword::fromString(' secret12');

    expect($password->value())->toBe(' secret12');
});

it('rejects a password that is too short', function () {
    PlainPassword::fromString('short');
})->throws(InvalidPassword::class);
