<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductImage;
use App\Product\Domain\ValueObject\ProductImage;

it('accepts a local media path', function () {
    $image = ProductImage::fromString('/media/products/001-tshirt-noir.svg');

    expect($image->value())->toBe('/media/products/001-tshirt-noir.svg');
});

it('accepts an http image URL', function () {
    $image = ProductImage::fromString('https://cdn.shoppy.test/tee.png');

    expect($image->value())->toBe('https://cdn.shoppy.test/tee.png');
});

it('trims surrounding whitespace', function () {
    $image = ProductImage::fromString('  /media/products/tee.jpg  ');

    expect($image->value())->toBe('/media/products/tee.jpg');
});

it('rejects an empty image', function () {
    ProductImage::fromString('   ');
})->throws(InvalidProductImage::class);

it('rejects a javascript URL', function () {
    ProductImage::fromString('javascript:alert(1)');
})->throws(InvalidProductImage::class);

it('rejects a path outside media products', function () {
    ProductImage::fromString('/etc/passwd');
})->throws(InvalidProductImage::class);
