<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidShippingMethod;
use App\Order\Domain\ValueObject\ShippingMethod;

it('charges standard delivery below 49 euros', function () {
    $method = ShippingMethod::quote(ShippingMethod::STANDARD, 4899);

    expect($method->code())->toBe('standard')
        ->and($method->label())->toBe('Standard')
        ->and($method->feeCents())->toBe(490);
});

it('offers standard delivery from 49 euros', function () {
    expect(ShippingMethod::quote(ShippingMethod::STANDARD, 4900)->feeCents())->toBe(0)
        ->and(ShippingMethod::quote(ShippingMethod::STANDARD, 8000)->feeCents())->toBe(0);
});

it('always charges express delivery', function () {
    expect(ShippingMethod::quote(ShippingMethod::EXPRESS, 8000)->feeCents())->toBe(990)
        ->and(ShippingMethod::quote(ShippingMethod::EXPRESS, 100)->label())->toBe('Express');
});

it('rejects an unknown method', function () {
    ShippingMethod::quote('drone', 1000);
})->throws(InvalidShippingMethod::class);
