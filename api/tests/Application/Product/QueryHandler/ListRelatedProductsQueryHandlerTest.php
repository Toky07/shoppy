<?php

declare(strict_types=1);

use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListRelatedProductsQuery;
use App\Product\Application\QueryHandler\ListRelatedProductsQueryHandler;
use App\Product\Domain\Entity\Category;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategoryName;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Infrastructure\Persistence\InMemoryCategoryRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('lists other published products from the same category', function () {
    $products = new InMemoryProductRepository();
    $categories = new InMemoryCategoryRepository();
    $category = Category::create(
        CategoryId::fromString('550e8400-e29b-41d4-a716-446655440010'),
        CategoryName::fromString('Textile'),
    );
    $categories->save($category);
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440002', 'Hoodie', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440003', 'Draft Cap', new DateTimeImmutable('2026-08-21T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440004', 'Mug', new DateTimeImmutable('2026-08-22T12:00:00+00:00'));

    foreach (['550e8400-e29b-41d4-a716-446655440001', '550e8400-e29b-41d4-a716-446655440002', '550e8400-e29b-41d4-a716-446655440003'] as $id) {
        $product = $products->findById(ProductId::fromString($id));
        $product->assignCategory($category->id());
        $products->save($product);
    }

    $draft = $products->findById(ProductId::fromString('550e8400-e29b-41d4-a716-446655440003'));
    $draft->changePublication(false);
    $products->save($draft);

    $response = (new ListRelatedProductsQueryHandler(
        $products,
        new ProductResponseFactory(new InMemoryMediaRepository(), $categories),
    ))->handle(new ListRelatedProductsQuery('550e8400-e29b-41d4-a716-446655440001'));

    expect(array_column($response->toArray()['items'], 'name'))->toBe(['Hoodie']);
});

it('does not expose related products of a draft', function () {
    $products = new InMemoryProductRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Draft Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $draft = $products->findById(ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'));
    $draft->changePublication(false);
    $products->save($draft);

    $handler = new ListRelatedProductsQueryHandler(
        $products,
        new ProductResponseFactory(new InMemoryMediaRepository(), new InMemoryCategoryRepository()),
    );

    expect(fn () => $handler->handle(new ListRelatedProductsQuery('550e8400-e29b-41d4-a716-446655440001')))
        ->toThrow(ProductNotFound::class);
});
