<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaId;
use App\Media\Domain\ValueObject\MediaId;

it('accepts a valid uuid', function () {
    $id = MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440000');

    expect($id->value())->toBe('aa0e8400-e29b-41d4-a716-446655440000');
});

it('generates a uuid', function () {
    $id = MediaId::generate();

    expect($id->value())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});

it('rejects an invalid uuid', function () {
    MediaId::fromString('not-a-uuid');
})->throws(InvalidMediaId::class);
