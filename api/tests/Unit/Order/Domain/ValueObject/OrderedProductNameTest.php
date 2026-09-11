<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidOrderedProductName;
use App\Order\Domain\ValueObject\OrderedProductName;

it('exposes the value it was created with', function () {
    $name = OrderedProductName::fromString('Nuvora Tee');

    expect($name->value())->toBe('Nuvora Tee');
});

it('trims surrounding whitespace', function () {
    $name = OrderedProductName::fromString('  Nuvora Tee  ');

    expect($name->value())->toBe('Nuvora Tee');
});

it('rejects an empty name', function () {
    OrderedProductName::fromString('   ');
})->throws(InvalidOrderedProductName::class);
