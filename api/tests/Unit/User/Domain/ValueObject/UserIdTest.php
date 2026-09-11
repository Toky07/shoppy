<?php

declare(strict_types=1);

use App\User\Domain\Exception\InvalidUserId;
use App\User\Domain\ValueObject\UserId;

it('rejects an invalid uuid', function () {
    UserId::fromString('not-a-uuid');
})->throws(InvalidUserId::class);

it('generates a valid uuid', function () {
    $id = UserId::generate();

    expect($id->value())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});
