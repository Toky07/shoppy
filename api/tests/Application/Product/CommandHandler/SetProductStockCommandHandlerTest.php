<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\SetProductStockCommand;
use App\Product\Application\CommandHandler\SetProductStockCommandHandler;
use App\Product\Domain\Exception\InvalidProductStock;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

it('sets product stock', function () {
    $repository = new InMemoryProductRepository();
    $id = createProducts(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    )->handle(new CreateProductCommand(name: 'Nuvora Tee', priceCents: 1999));

    (new SetProductStockCommandHandler($repository))->handle(new SetProductStockCommand(
        id: $id->value(),
        stock: 42,
    ));

    expect($repository->findById($id)?->stock()->value())->toBe(42);
});

it('rejects a missing product', function () {
    (new SetProductStockCommandHandler(new InMemoryProductRepository()))->handle(new SetProductStockCommand(
        id: '550e8400-e29b-41d4-a716-446655440000',
        stock: 10,
    ));
})->throws(ProductNotFound::class);

it('rejects negative stock', function () {
    $repository = new InMemoryProductRepository();
    $id = createProducts(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    )->handle(new CreateProductCommand(name: 'Nuvora Tee', priceCents: 1999));

    (new SetProductStockCommandHandler($repository))->handle(new SetProductStockCommand(
        id: $id->value(),
        stock: -1,
    ));
})->throws(InvalidProductStock::class);
