<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductDescription;
use App\Product\Domain\ValueObject\ProductDescription;

it('exposes the value it was created with', function () {
    $description = ProductDescription::fromString('Soft cotton t-shirt');

    expect($description->value())->toBe('Soft cotton t-shirt');
});

it('rejects an empty description', function () {
    ProductDescription::fromString('   ');
})->throws(InvalidProductDescription::class);
