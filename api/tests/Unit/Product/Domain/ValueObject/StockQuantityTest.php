<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductStock;
use App\Product\Domain\ValueObject\StockQuantity;

it('exposes a non-negative stock quantity', function () {
    expect(StockQuantity::fromInt(10)->value())->toBe(10)
        ->and(StockQuantity::zero()->value())->toBe(0);
});

it('rejects a negative stock quantity', function () {
    StockQuantity::fromInt(-1);
})->throws(InvalidProductStock::class);

it('adds and subtracts stock', function () {
    $stock = StockQuantity::fromInt(10);

    expect($stock->add(5)->value())->toBe(15)
        ->and($stock->subtract(3)->value())->toBe(7)
        ->and($stock->isAtLeast(10))->toBeTrue()
        ->and($stock->isAtLeast(11))->toBeFalse();
});

it('rejects subtracting below zero', function () {
    StockQuantity::fromInt(2)->subtract(3);
})->throws(InvalidProductStock::class);
