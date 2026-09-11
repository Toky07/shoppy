<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\CommandHandler\DeleteProductCommandHandler;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

it('deletes a product', function () {
    $repository = new InMemoryProductRepository();
    $id = (new CreateProductCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new CreateProductCommand(name: 'Nuvora Tee', priceCents: 1999));

    (new DeleteProductCommandHandler($repository))->handle(new DeleteProductCommand($id->value()));

    expect($repository->findById($id))->toBeNull();
});

it('fails when deleting an unknown product', function () {
    (new DeleteProductCommandHandler(new InMemoryProductRepository()))->handle(
        new DeleteProductCommand('550e8400-e29b-41d4-a716-446655440000'),
    );
})->throws(ProductNotFound::class);
