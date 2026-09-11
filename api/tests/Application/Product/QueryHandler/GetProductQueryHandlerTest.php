<?php

declare(strict_types=1);

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

    $response = (new GetProductQueryHandler($repository))->handle(
        new GetProductQuery($id->value()),
    );

    expect($response->toArray())->toBe([
        'id' => '550e8400-e29b-41d4-a716-446655440000',
        'name' => 'Nuvora Tee',
        'description' => 'Soft cotton t-shirt',
        'price' => [
            'cents' => 1999,
            'currency' => 'EUR',
        ],
        'stock' => 0,
        'imageUrl' => null,
        'createdAt' => '2026-08-20T12:00:00+00:00',
    ]);
});

it('fails when the product does not exist', function () {
    $handler = new GetProductQueryHandler(new InMemoryProductRepository());

    $handler->handle(new GetProductQuery('550e8400-e29b-41d4-a716-446655440000'));
})->throws(ProductNotFound::class);
