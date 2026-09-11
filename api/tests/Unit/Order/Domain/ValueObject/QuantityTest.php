<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidOrderQuantity;
use App\Order\Domain\ValueObject\Quantity;

it('exposes the quantity it was created with', function () {
    expect(Quantity::fromInt(2)->value())->toBe(2);
});

it('rejects a quantity below one', function () {
    Quantity::fromInt(0);
})->throws(InvalidOrderQuantity::class);
