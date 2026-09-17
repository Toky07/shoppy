<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSlug;

it('builds a slug from a product name', function () {
    expect(ProductSlug::fromName(ProductName::fromString('Coque Wave'))->value())->toBe('coque-wave');
});

it('strips accents from a french name', function () {
    expect(ProductSlug::fromName(ProductName::fromString('Chaussettes Cérisées'))->value())
        ->toBe('chaussettes-cerisees');
});

it('accepts an explicit slug', function () {
    expect(ProductSlug::fromString('nuvora-tee')->value())->toBe('nuvora-tee');
});

it('appends a copy number when the base slug is taken', function () {
    $slug = ProductSlug::fromString('nuvora-tee');

    expect($slug->withCopyNumber(2)->value())->toBe('nuvora-tee-2')
        ->and($slug->withCopyNumber(1)->value())->toBe('nuvora-tee');
});

it('rejects an empty slug', function () {
    ProductSlug::fromString('');
})->throws(InvalidProductSlug::class);

it('rejects uppercase characters', function () {
    ProductSlug::fromString('Nuvora-Tee');
})->throws(InvalidProductSlug::class);

it('falls back to produit when the name has no letters', function () {
    expect(ProductSlug::fromName(ProductName::fromString('!!!'))->value())->toBe('produit');
});
