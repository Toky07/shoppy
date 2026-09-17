<?php

declare(strict_types=1);

use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('keeps the base slug when it is free', function () {
    $allocator = new UniqueProductSlug(new InMemoryProductRepository());

    expect($allocator->allocate(ProductName::fromString('Coque Wave'))->value())->toBe('coque-wave');
});

it('appends a copy number when the base slug is taken', function () {
    $repository = new InMemoryProductRepository();
    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ProductName::fromString('Coque Wave'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        slug: ProductSlug::fromString('coque-wave'),
    ));

    $allocator = new UniqueProductSlug($repository);

    expect($allocator->allocate(ProductName::fromString('Coque Wave'))->value())->toBe('coque-wave-2');
});

it('ignores the current product when reallocating its slug', function () {
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
    $repository = new InMemoryProductRepository();
    $repository->save(Product::create(
        $id,
        ProductName::fromString('Coque Wave'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        slug: ProductSlug::fromString('coque-wave'),
    ));

    $allocator = new UniqueProductSlug($repository);

    expect($allocator->allocate(ProductName::fromString('Coque Wave'), $id)->value())->toBe('coque-wave');
});
