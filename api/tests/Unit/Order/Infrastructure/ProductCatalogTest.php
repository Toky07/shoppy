<?php

declare(strict_types=1);

use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Infrastructure\Catalog\ProductCatalog;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('maps a catalog product to a snapshot', function () {
    $products = new InMemoryProductRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $products->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $snapshot = (new ProductCatalog($products))->findById(CatalogProductId::fromString($id->value()));

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->id)->toBe($id->value())
        ->and($snapshot->name)->toBe('Nuvora Tee')
        ->and($snapshot->unitPriceCents)->toBe(1999)
        ->and($snapshot->stock)->toBe(0);
});

it('returns null when the catalog product does not exist', function () {
    $snapshot = (new ProductCatalog(new InMemoryProductRepository()))->findById(
        CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
    );

    expect($snapshot)->toBeNull();
});
