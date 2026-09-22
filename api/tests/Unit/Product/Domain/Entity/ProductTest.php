<?php

declare(strict_types=1);

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InsufficientProductStock;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;

function createProduct(
    ?ProductDescription $description = null,
    ?ProductPrice $price = null,
    ?DateTimeImmutable $createdAt = null,
): Product {
    return Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        ProductName::fromString('Nuvora Tee'),
        $price ?? ProductPrice::fromCents(1999),
        $createdAt ?? new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        $description,
    );
}

it('exposes its identity and required fields', function () {
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $name = ProductName::fromString('Nuvora Tee');
    $price = ProductPrice::fromCents(1999);
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $product = Product::create($id, $name, $price, $createdAt);

    expect($product->id())->toBe($id)
        ->and($product->name())->toBe($name)
        ->and($product->price())->toBe($price)
        ->and($product->createdAt())->toBe($createdAt)
        ->and($product->description())->toBeNull()
        ->and($product->stock()->value())->toBe(0)
        ->and($product->slug()->value())->toBe('nuvora-tee');
});

it('exposes its description when provided', function () {
    $description = ProductDescription::fromString('Soft cotton t-shirt');

    $product = createProduct(description: $description);

    expect($product->description())->toBe($description);
});

it('creates a product with an initial stock', function () {
    $product = Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        stock: StockQuantity::fromInt(12),
    );

    expect($product->stock()->value())->toBe(12);
});

it('renames the product', function () {
    $product = createProduct();
    $name = ProductName::fromString('Nuvora Hoodie');

    $product->rename($name);

    expect($product->name())->toBe($name);
});

it('changes the product price', function () {
    $product = createProduct();
    $price = ProductPrice::fromCents(4999);

    $product->changePrice($price);

    expect($product->price())->toBe($price);
});

it('changes the product description', function () {
    $product = createProduct();
    $description = ProductDescription::fromString('Organic cotton hoodie');

    $product->changeDescription($description);

    expect($product->description())->toBe($description);
});

it('clears the product description', function () {
    $product = createProduct(description: ProductDescription::fromString('Soft cotton t-shirt'));

    $product->changeDescription(null);

    expect($product->description())->toBeNull();
});

it('sets the product stock', function () {
    $product = createProduct();

    $product->setStock(StockQuantity::fromInt(25));

    expect($product->stock()->value())->toBe(25);
});

it('decreases stock when enough is available', function () {
    $product = createProduct();
    $product->setStock(StockQuantity::fromInt(10));

    $product->decreaseStock(4);

    expect($product->stock()->value())->toBe(6);
});

it('rejects decreasing stock when insufficient', function () {
    $product = createProduct();
    $product->setStock(StockQuantity::fromInt(2));

    $product->decreaseStock(3);
})->throws(InsufficientProductStock::class);

it('increases stock', function () {
    $product = createProduct();
    $product->setStock(StockQuantity::fromInt(2));

    $product->increaseStock(5);

    expect($product->stock()->value())->toBe(7);
});

it('sells a specific size and keeps the product stock equal to the variants', function () {
    $medium = \App\Product\Domain\Entity\ProductVariant::create(
        \App\Product\Domain\ValueObject\VariantId::fromString('550e8400-e29b-41d4-a716-446655440010'),
        \App\Product\Domain\ValueObject\ProductSku::fromString('NUVORA-TEE-M'),
        \App\Product\Domain\ValueObject\VariantLabel::fromString('M', 'size'),
        \App\Product\Domain\ValueObject\VariantLabel::fromString('Noir', 'color'),
        StockQuantity::fromInt(4),
    );
    $product = Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        null,
        null,
        null,
        null,
        \App\Product\Domain\ValueObject\ProductSku::fromString('NUVORA-TEE'),
        [$medium],
    );

    $product->decreaseStock(1, $medium->id());

    expect($product->sku()->value())->toBe('NUVORA-TEE')
        ->and($medium->stock()->value())->toBe(3)
        ->and($product->stock()->value())->toBe(3);
});
