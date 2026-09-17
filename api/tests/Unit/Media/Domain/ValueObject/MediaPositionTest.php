<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaPosition;
use App\Media\Domain\ValueObject\MediaPosition;

it('accepts a zero-based position', function () {
    expect(MediaPosition::fromInt(0)->value())->toBe(0)
        ->and(MediaPosition::fromInt(3)->value())->toBe(3);
});

it('rejects a negative position', function () {
    MediaPosition::fromInt(-1);
})->throws(InvalidMediaPosition::class);
