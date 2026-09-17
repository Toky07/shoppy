<?php

declare(strict_types=1);

use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\QueryHandler\ListProductsQueryHandler;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductPagination;
use App\Product\Domain\Exception\InvalidProductSearch;
use App\Product\Domain\Exception\InvalidProductSort;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

function saveProduct(
    InMemoryProductRepository $repository,
    string $id,
    string $name,
    DateTimeImmutable $createdAt,
    int $priceCents = 1999,
    ?string $description = null,
): void {
    $repository->save(Product::create(
        ProductId::fromString($id),
        ProductName::fromString($name),
        ProductPrice::fromCents($priceCents),
        $createdAt,
        $description === null ? null : ProductDescription::fromString($description),
    ));
}

function listProductsHandler(InMemoryProductRepository $repository): ListProductsQueryHandler
{
    return new ListProductsQueryHandler($repository, new ProductResponseFactory(new InMemoryMediaRepository()));
}

it('lists products newest first', function () {
    $repository = new InMemoryProductRepository();
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'Older Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Newer Tee', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (listProductsHandler($repository))->handle(new ListProductsQuery());

    expect($response->toArray())->toMatchArray([
        'page' => 1,
        'limit' => 20,
        'total' => 2,
    ])
        ->and($response->toArray()['items'][0]['name'])->toBe('Newer Tee')
        ->and($response->toArray()['items'][1]['name'])->toBe('Older Tee');
});

it('paginates products', function () {
    $repository = new InMemoryProductRepository();
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'One', new DateTimeImmutable('2026-08-18T12:00:00+00:00'));
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Two', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440003', 'Three', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (listProductsHandler($repository))->handle(new ListProductsQuery(page: 2, limit: 2));

    expect($response->total)->toBe(3)
        ->and($response->page)->toBe(2)
        ->and($response->limit)->toBe(2)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->name)->toBe('One');
});

it('rejects an invalid page', function () {
    (listProductsHandler(new InMemoryProductRepository()))->handle(new ListProductsQuery(page: 0));
})->throws(InvalidProductPagination::class);

it('searches products by name and description', function () {
    $repository = new InMemoryProductRepository();
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'Nuvora Tee', new DateTimeImmutable('2026-08-18T12:00:00+00:00'), 1999, 'Soft cotton');
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Nuvora Hoodie', new DateTimeImmutable('2026-08-19T12:00:00+00:00'), 4999);
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440003', 'Ceramic Mug', new DateTimeImmutable('2026-08-20T12:00:00+00:00'), 1299, 'Nuvora logo');

    $byName = (listProductsHandler($repository))->handle(new ListProductsQuery(search: 'hoodie'));
    $byDescription = (listProductsHandler($repository))->handle(new ListProductsQuery(search: 'logo'));

    expect(array_column($byName->toArray()['items'], 'name'))->toBe(['Nuvora Hoodie'])
        ->and($byName->total)->toBe(1)
        ->and(array_column($byDescription->toArray()['items'], 'name'))->toBe(['Ceramic Mug']);
});

it('sorts products by price ascending', function () {
    $repository = new InMemoryProductRepository();
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'Hoodie', new DateTimeImmutable('2026-08-20T12:00:00+00:00'), 4999);
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Mug', new DateTimeImmutable('2026-08-19T12:00:00+00:00'), 1299);
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440003', 'Tee', new DateTimeImmutable('2026-08-18T12:00:00+00:00'), 1999);

    $response = (listProductsHandler($repository))->handle(new ListProductsQuery(sort: 'price_asc'));

    expect(array_column($response->toArray()['items'], 'name'))->toBe(['Mug', 'Tee', 'Hoodie']);
});

it('rejects an invalid sort', function () {
    (listProductsHandler(new InMemoryProductRepository()))->handle(new ListProductsQuery(sort: 'popularity'));
})->throws(InvalidProductSort::class);

it('rejects a search that is too long', function () {
    (listProductsHandler(new InMemoryProductRepository()))->handle(
        new ListProductsQuery(search: str_repeat('a', 101)),
    );
})->throws(InvalidProductSearch::class);
