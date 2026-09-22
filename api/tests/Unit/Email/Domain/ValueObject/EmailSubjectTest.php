<?php

declare(strict_types=1);

use App\Email\Domain\Exception\InvalidEmailSubject;
use App\Email\Domain\ValueObject\EmailSubject;

it('trims a subject', function () {
    $subject = EmailSubject::fromString('  Bienvenue  ');

    expect($subject->value())->toBe('Bienvenue');
});

it('rejects an empty subject', function () {
    EmailSubject::fromString('   ');
})->throws(InvalidEmailSubject::class);

it('rejects a subject with a line break', function () {
    EmailSubject::fromString("Bienvenue\r\nBcc: evil@shoppy.test");
})->throws(InvalidEmailSubject::class);

it('rejects a subject longer than 150 characters', function () {
    EmailSubject::fromString(str_repeat('a', 151));
})->throws(InvalidEmailSubject::class);
