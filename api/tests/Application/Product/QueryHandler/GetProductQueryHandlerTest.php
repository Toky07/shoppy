<?php

declare(strict_types=1);

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
use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('returns a product by id', function () {
    $repository = new InMemoryProductRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $repository->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        $createdAt,
        ProductDescription::fromString('Soft cotton t-shirt'),
    ));

    $response = (new GetProductQueryHandler(
        $repository,
        new ProductResponseFactory(new InMemoryMediaRepository(), new \App\Product\Infrastructure\Persistence\InMemoryCategoryRepository()),
    ))->handle(new GetProductQuery($id->value()));

    expect($response->toArray())->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'slug' => 'nuvora-tee',
        'name' => 'Nuvora Tee',
        'description' => 'Soft cotton t-shirt',
        'price' => [
            'cents' => 1999,
            'currency' => 'EUR',
        ],
        'stock' => 0,
        'imageUrl' => null,
        'imageUrls' => [],
        'createdAt' => '2026-08-20T12:00:00+00:00',
        'category' => null,
    ]);
});

it('returns a product by slug', function () {
    $repository = new InMemoryProductRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');

    $repository->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $response = (new GetProductQueryHandler(
        $repository,
        new ProductResponseFactory(new InMemoryMediaRepository(), new \App\Product\Infrastructure\Persistence\InMemoryCategoryRepository()),
    ))->handle(new GetProductQuery('nuvora-tee'));

    expect($response->id)->toBe($id->value())
        ->and($response->slug)->toBe('nuvora-tee');
});


it('composes product images from the media module', function () {
    $repository = new InMemoryProductRepository();
    $media = new InMemoryMediaRepository();
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');

    $repository->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
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
    $media->save(Media::create(
        MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440002'),
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString($id->value()),
        MediaFilename::fromString('two.png'),
        MediaRelativePath::fromString('2026/09/two.png'),
        MediaMimeType::fromString('image/png'),
        MediaSize::fromInt(12),
        MediaPosition::fromInt(1),
        new DateTimeImmutable('2026-09-16T12:00:00+00:00'),
    ));

    $response = (new GetProductQueryHandler(
        $repository,
        new ProductResponseFactory($media, new \App\Product\Infrastructure\Persistence\InMemoryCategoryRepository()),
    ))->handle(new GetProductQuery($id->value()));

    expect($response->imageUrl)->toBe('/uploads/2026/09/one.png')
        ->and($response->imageUrls)->toBe([
            '/uploads/2026/09/one.png',
            '/uploads/2026/09/two.png',
        ]);
});

it('fails when the product does not exist', function () {
    $handler = new GetProductQueryHandler(
        new InMemoryProductRepository(),
        new ProductResponseFactory(new InMemoryMediaRepository(), new \App\Product\Infrastructure\Persistence\InMemoryCategoryRepository()),
    );

    $handler->handle(new GetProductQuery('550e8400-e29b-41d4-a716-446655440000'));
})->throws(ProductNotFound::class);
