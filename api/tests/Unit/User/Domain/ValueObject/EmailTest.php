<?php

declare(strict_types=1);

use App\User\Domain\Exception\InvalidUserEmail;
use App\User\Domain\ValueObject\Email;

it('normalizes email case and whitespace', function () {
    $email = Email::fromString('  Ada@Nuvora.test  ');

    expect($email->value())->toBe('ada@nuvora.test');
});

it('rejects an empty email', function () {
    Email::fromString('   ');
})->throws(InvalidUserEmail::class);

it('rejects an invalid email', function () {
    Email::fromString('not-an-email');
})->throws(InvalidUserEmail::class);
