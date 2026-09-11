<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductName;
use App\Product\Domain\ValueObject\ProductName;

it('exposes the value it was created with', function () {
    $name = ProductName::fromString('Nuvora Tee');

    expect($name->value())->toBe('Nuvora Tee');
});

it('trims surrounding whitespace', function () {
    $name = ProductName::fromString('  Nuvora Tee  ');

    expect($name->value())->toBe('Nuvora Tee');
});

it('rejects an empty name', function () {
    ProductName::fromString('   ');
})->throws(InvalidProductName::class);
