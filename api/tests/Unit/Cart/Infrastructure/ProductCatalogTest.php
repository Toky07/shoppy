<?php

declare(strict_types=1);

use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Infrastructure\Catalog\ProductCatalog;
use App\Media\Domain\Entity\Media;
use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('includes the first product image in the cart snapshot', function () {
    $products = new InMemoryProductRepository();
    $media = new InMemoryMediaRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $products->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        stock: StockQuantity::fromInt(10),
    ));
    $media->save(Media::create(
        MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440001'),
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString($id->value()),
        MediaFilename::fromString('one.png'),
        MediaRelativePath::fromString('2026/09/one.png'),
        MediaMimeType::fromString('image/png'),
        MediaSize::fromInt(12),
        MediaPosition::fromInt(0),
        new DateTimeImmutable('2026-09-16T12:00:00+00:00'),
    ));

    $snapshot = (new ProductCatalog($products, $media))->findById(CartProductId::fromString($id->value()));

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->name)->toBe('Nuvora Tee')
        ->and($snapshot->imageUrl)->toBe('/uploads/2026/09/one.png');
});

it('returns a snapshot without an image when the product has none', function () {
    $products = new InMemoryProductRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $products->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $snapshot = (new ProductCatalog($products, new InMemoryMediaRepository()))
        ->findById(CartProductId::fromString($id->value()));

    expect($snapshot?->imageUrl)->toBeNull();
});
