<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductPrice;
use App\Product\Domain\ValueObject\ProductPrice;

it('exposes the amount in cents and euro currency', function () {
    $price = ProductPrice::fromCents(1999);

    expect($price->cents())->toBe(1999)
        ->and($price->currency())->toBe('EUR');
});

it('allows a free product', function () {
    $price = ProductPrice::fromCents(0);

    expect($price->cents())->toBe(0);
});

it('rejects a negative amount', function () {
    ProductPrice::fromCents(-1);
})->throws(InvalidProductPrice::class);
