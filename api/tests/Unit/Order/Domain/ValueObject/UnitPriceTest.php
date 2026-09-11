<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidUnitPrice;
use App\Order\Domain\ValueObject\UnitPrice;

it('exposes the amount in cents and euro currency', function () {
    $price = UnitPrice::fromCents(1999);

    expect($price->cents())->toBe(1999)
        ->and($price->currency())->toBe('EUR');
});

it('allows a free unit price', function () {
    expect(UnitPrice::fromCents(0)->cents())->toBe(0);
});

it('rejects a negative amount', function () {
    UnitPrice::fromCents(-1);
})->throws(InvalidUnitPrice::class);
