<?php

declare(strict_types=1);

use App\Email\Domain\Exception\InvalidEmailAttachment;
use App\Email\Domain\ValueObject\EmailAttachment;

it('accepts a named file', function () {
    $attachment = EmailAttachment::fromContent('recu.txt', 'text/plain', 'Bonjour');

    expect($attachment->filename())->toBe('recu.txt')
        ->and($attachment->mimeType())->toBe('text/plain')
        ->and($attachment->content())->toBe('Bonjour');
});

it('normalizes the mime type', function () {
    $attachment = EmailAttachment::fromContent('recu.txt', ' Text/Plain ', 'Bonjour');

    expect($attachment->mimeType())->toBe('text/plain');
});

it('rejects an empty file', function () {
    EmailAttachment::fromContent('recu.txt', 'text/plain', '');
})->throws(InvalidEmailAttachment::class);

it('rejects a file larger than 10 megabytes', function () {
    EmailAttachment::fromContent('recu.txt', 'text/plain', str_repeat('a', (10 * 1024 * 1024) + 1));
})->throws(InvalidEmailAttachment::class);

it('rejects a filename with a path or a line break', function (string $filename) {
    EmailAttachment::fromContent($filename, 'text/plain', 'Bonjour');
})->throws(InvalidEmailAttachment::class)->with([
    '../secret.txt',
    'dossier/recu.txt',
    "recu.txt\nBcc: evil@shoppy.test",
    '   ',
]);

it('rejects an invalid mime type', function () {
    EmailAttachment::fromContent('recu.txt', 'not a mime', 'Bonjour');
})->throws(InvalidEmailAttachment::class);
