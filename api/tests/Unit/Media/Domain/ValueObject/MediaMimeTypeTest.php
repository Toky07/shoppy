<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaMimeType;
use App\Media\Domain\ValueObject\MediaMimeType;

it('accepts supported image mime types', function (string $mime, string $extension) {
    $type = MediaMimeType::fromString($mime);

    expect($type->value())->toBe($mime)
        ->and($type->extension())->toBe($extension);
})->with([
    ['image/jpeg', 'jpg'],
    ['image/png', 'png'],
    ['image/webp', 'webp'],
    ['image/gif', 'gif'],
    ['image/svg+xml', 'svg'],
]);

it('rejects an unsupported mime type', function () {
    MediaMimeType::fromString('application/pdf');
})->throws(InvalidMediaMimeType::class);
