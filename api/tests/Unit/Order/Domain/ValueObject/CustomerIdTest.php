<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidCustomerId;
use App\Order\Domain\ValueObject\CustomerId;

it('rejects an invalid uuid', function () {
    CustomerId::fromString('not-a-uuid');
})->throws(InvalidCustomerId::class);

it('exposes the value it was created with', function () {
    $id = CustomerId::fromString('11111111-1111-4111-8111-111111111111');

    expect($id->value())->toBe('11111111-1111-4111-8111-111111111111');
});
