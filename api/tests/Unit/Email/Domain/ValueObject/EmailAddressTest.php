<?php

declare(strict_types=1);

use App\Email\Domain\Exception\InvalidEmailAddress;
use App\Email\Domain\ValueObject\EmailAddress;

it('normalizes an email address', function () {
    $address = EmailAddress::fromString('  Ada@Shoppy.test  ');

    expect($address->value())->toBe('ada@shoppy.test');
});

it('rejects an empty email address', function () {
    EmailAddress::fromString('   ');
})->throws(InvalidEmailAddress::class);

it('rejects an invalid email address', function () {
    EmailAddress::fromString('not-an-email');
})->throws(InvalidEmailAddress::class);

it('rejects an email address with a line break', function () {
    EmailAddress::fromString("ada@shoppy.test\nBcc: evil@shoppy.test");
})->throws(InvalidEmailAddress::class);
