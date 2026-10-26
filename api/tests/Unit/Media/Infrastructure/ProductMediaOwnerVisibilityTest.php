<?php

declare(strict_types=1);

use App\Media\Infrastructure\Catalog\ProductMediaOwnerVisibility;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

function productVisibilityCatalog(): InMemoryProductRepository
{
    $products = new InMemoryProductRepository();
    $products->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ProductName::fromString('Published Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    $draft = Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440002'),
        ProductName::fromString('Draft Mug'),
        ProductPrice::fromCents(1299),
        new DateTimeImmutable('2026-08-21T12:00:00+00:00'),
        published: false,
    );
    $products->save($draft);

    return $products;
}

it('treats a published product as publicly listable', function () {
    $visibility = new ProductMediaOwnerVisibility(productVisibilityCatalog());

    expect($visibility->isPubliclyListable('product', '550e8400-e29b-41d4-a716-446655440001'))->toBeTrue()
        ->and($visibility->isPubliclyListable(
            'App\\Product\\Domain\\Entity\\Product',
            '550e8400-e29b-41d4-a716-446655440001',
        ))->toBeTrue();
});

it('hides unpublished and unknown products from public listing', function () {
    $visibility = new ProductMediaOwnerVisibility(productVisibilityCatalog());

    expect($visibility->isPubliclyListable('product', '550e8400-e29b-41d4-a716-446655440002'))->toBeFalse()
        ->and($visibility->isPubliclyListable('product', '550e8400-e29b-41d4-a716-446655440099'))->toBeFalse()
        ->and($visibility->isPubliclyListable('category', '550e8400-e29b-41d4-a716-446655440001'))->toBeFalse();
});
