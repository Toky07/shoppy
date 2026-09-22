<?php

declare(strict_types=1);

use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Application\ProductResponseFactory;
use App\Product\Application\Query\ListProductsQuery;
use App\Product\Application\QueryHandler\ListProductsQueryHandler;
use App\Product\Domain\Entity\Category;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductPagination;
use App\Product\Domain\Exception\InvalidProductSearch;
use App\Product\Domain\Exception\InvalidProductSort;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategoryName;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Infrastructure\Persistence\InMemoryCategoryRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

function saveProduct(
    InMemoryProductRepository $repository,
    string $id,
    string $name,
    DateTimeImmutable $createdAt,
    int $priceCents = 1999,
    ?string $description = null,
    int $stock = 0,
): void {
    $repository->save(Product::create(
        ProductId::fromString($id),
        ProductName::fromString($name),
        ProductPrice::fromCents($priceCents),
        $createdAt,
        $description === null ? null : ProductDescription::fromString($description),
        StockQuantity::fromInt($stock),
    ));
}

function listProductsHandler(
    InMemoryProductRepository $repository,
    ?CategoryRepository $categories = null,
): ListProductsQueryHandler {
    $categories ??= new InMemoryCategoryRepository();

    return new ListProductsQueryHandler(
        $repository,
        new ProductResponseFactory(new InMemoryMediaRepository(), $categories),
        $categories,
    );
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

it('filters by price and available stock', function () {
    $repository = new InMemoryProductRepository();
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'Mug', new DateTimeImmutable('2026-08-18T12:00:00+00:00'), 1299, stock: 0);
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'), 1999, stock: 4);
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440003', 'Hoodie', new DateTimeImmutable('2026-08-20T12:00:00+00:00'), 4999, stock: 2);

    $response = (listProductsHandler($repository))->handle(new ListProductsQuery(
        minPriceCents: 1500,
        maxPriceCents: 3000,
        inStockOnly: true,
    ));

    expect(array_column($response->toArray()['items'], 'name'))->toBe(['Tee'])
        ->and($response->total)->toBe(1);
});

it('loads images for a page in one media lookup', function () {
    $repository = new InMemoryProductRepository();
    $inner = new InMemoryMediaRepository();
    $media = new class($inner) implements \App\Media\Domain\Repository\MediaRepository {
        public int $batchedLookups = 0;

        public function __construct(private InMemoryMediaRepository $inner)
        {
        }

        public function save(\App\Media\Domain\Entity\Media $media): void
        {
            $this->inner->save($media);
        }

        public function findById(\App\Media\Domain\ValueObject\MediaId $id): ?\App\Media\Domain\Entity\Media
        {
            return $this->inner->findById($id);
        }

        public function findByOwner(\App\Media\Domain\ValueObject\MediaOwnerType $ownerType, \App\Media\Domain\ValueObject\MediaOwnerId $ownerId): array
        {
            return $this->inner->findByOwner($ownerType, $ownerId);
        }

        public function findByOwners(\App\Media\Domain\ValueObject\MediaOwnerType $ownerType, array $ownerIds): array
        {
            ++$this->batchedLookups;

            return $this->inner->findByOwners($ownerType, $ownerIds);
        }

        public function delete(\App\Media\Domain\Entity\Media $media): void
        {
            $this->inner->delete($media);
        }
    };
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($repository, '550e8400-e29b-41d4-a716-446655440002', 'Mug', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    (new ListProductsQueryHandler(
        $repository,
        new ProductResponseFactory($media, new InMemoryCategoryRepository()),
        new InMemoryCategoryRepository(),
    ))->handle(new ListProductsQuery());

    expect($media->batchedLookups)->toBe(1);
});

it('filters products by category and ignores an unknown slug', function () {
    $products = new InMemoryProductRepository();
    $categories = new InMemoryCategoryRepository();
    $category = Category::create(
        CategoryId::fromString('550e8400-e29b-41d4-a716-446655440010'),
        CategoryName::fromString('Textile'),
    );
    $categories->save($category);
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440002', 'Mug', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));
    $tee = $products->findById(ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'));
    $tee->assignCategory($category->id());
    $products->save($tee);

    $filtered = (listProductsHandler($products, $categories))->handle(new ListProductsQuery(categorySlug: 'textile'));
    $missing = (listProductsHandler($products, $categories))->handle(new ListProductsQuery(categorySlug: 'missing'));

    expect(array_column($filtered->toArray()['items'], 'name'))->toBe(['Tee'])
        ->and($filtered->toArray()['items'][0]['category'])->toBe([
            'id' => '550e8400-e29b-41d4-a716-446655440010',
            'name' => 'Textile',
            'slug' => 'textile',
        ])
        ->and($missing->toArray()['items'])->toBe([])
        ->and($missing->total)->toBe(0);
});
