<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaRelativePath;
use App\Media\Domain\ValueObject\MediaRelativePath;

it('accepts a wordpress-style year and month path', function () {
    $path = MediaRelativePath::fromString('2026/09/nuvora-tee.jpg');

    expect($path->value())->toBe('2026/09/nuvora-tee.jpg')
        ->and($path->publicUrl())->toBe('/uploads/2026/09/nuvora-tee.jpg');
});

it('rejects a leading slash', function () {
    MediaRelativePath::fromString('/uploads/2026/09/tee.jpg');
})->throws(InvalidMediaRelativePath::class);

it('rejects a path traversal', function () {
    MediaRelativePath::fromString('2026/09/../tee.jpg');
})->throws(InvalidMediaRelativePath::class);

it('rejects an empty path', function () {
    MediaRelativePath::fromString('');
})->throws(InvalidMediaRelativePath::class);
