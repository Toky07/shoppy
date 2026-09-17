<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaFilename;
use App\Media\Domain\ValueObject\MediaFilename;

it('accepts a sanitized filename', function () {
    $filename = MediaFilename::fromString('nuvora-tee.jpg');

    expect($filename->value())->toBe('nuvora-tee.jpg');
});

it('rejects an empty filename', function () {
    MediaFilename::fromString('  ');
})->throws(InvalidMediaFilename::class);

it('rejects a path traversal filename', function () {
    MediaFilename::fromString('../secret.jpg');
})->throws(InvalidMediaFilename::class);

it('rejects a filename with a directory separator', function () {
    MediaFilename::fromString('2026/09/tee.jpg');
})->throws(InvalidMediaFilename::class);
