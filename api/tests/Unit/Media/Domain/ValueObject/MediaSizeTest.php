<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaSize;
use App\Media\Domain\ValueObject\MediaSize;

it('accepts a size within the allowed range', function () {
    $size = MediaSize::fromInt(1024);

    expect($size->value())->toBe(1024);
});

it('rejects an empty file', function () {
    MediaSize::fromInt(0);
})->throws(InvalidMediaSize::class);

it('rejects a file larger than 10 mb', function () {
    MediaSize::fromInt((10 * 1024 * 1024) + 1);
})->throws(InvalidMediaSize::class);
