<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\CommandHandler\UpdateProductCommandHandler;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

function productRepositoryWithTee(): array
{
    $repository = new InMemoryProductRepository();
    $handler = new CreateProductCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $id = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        description: 'Soft cotton t-shirt',
    ));

    return [$repository, $id];
}

it('updates provided product fields', function () {
    [$repository, $id] = productRepositoryWithTee();

    (new UpdateProductCommandHandler($repository))->handle(new UpdateProductCommand(
        id: $id->value(),
        name: 'Nuvora Hoodie',
        priceCents: 4999,
        descriptionProvided: true,
        description: 'Organic cotton hoodie',
    ));

    $product = $repository->findById($id);

    expect($product)->not->toBeNull()
        ->and($product->name()->value())->toBe('Nuvora Hoodie')
        ->and($product->price()->cents())->toBe(4999)
        ->and($product->description()?->value())->toBe('Organic cotton hoodie')
        ->and($product->createdAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:00:00+00:00');
});

it('clears the description when null is provided', function () {
    [$repository, $id] = productRepositoryWithTee();

    (new UpdateProductCommandHandler($repository))->handle(new UpdateProductCommand(
        id: $id->value(),
        descriptionProvided: true,
        description: null,
    ));

    $product = $repository->findById($id);

    expect($product)->not->toBeNull()
        ->and($product->name()->value())->toBe('Nuvora Tee')
        ->and($product->description())->toBeNull();
});

it('fails when updating an unknown product', function () {
    (new UpdateProductCommandHandler(new InMemoryProductRepository()))->handle(new UpdateProductCommand(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Hoodie',
    ));
})->throws(ProductNotFound::class);
