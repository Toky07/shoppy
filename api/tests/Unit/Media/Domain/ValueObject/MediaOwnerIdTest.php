<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerId;

it('accepts a valid uuid', function () {
    $id = MediaOwnerId::fromString('550e8400-e29b-41d4-a716-446655440000');

    expect($id->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
});

it('rejects an invalid uuid', function () {
    MediaOwnerId::fromString('product-1');
})->throws(InvalidMediaOwnerId::class);
