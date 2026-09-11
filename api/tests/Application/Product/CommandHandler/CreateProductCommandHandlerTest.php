<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

it('creates and persists a product', function () {
    $repository = new InMemoryProductRepository();
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = new CreateProductCommandHandler($repository, new FixedClock($createdAt));

    $productId = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        description: 'Soft cotton t-shirt',
    ));

    $product = $repository->findById($productId);

    expect($product)->not->toBeNull()
        ->and($product->name()->value())->toBe('Nuvora Tee')
        ->and($product->price()->cents())->toBe(1999)
        ->and($product->price()->currency())->toBe('EUR')
        ->and($product->description()?->value())->toBe('Soft cotton t-shirt')
        ->and($product->stock()->value())->toBe(0)
        ->and($product->createdAt())->toBe($createdAt)
        ->and($product->image())->toBeNull()
        ->and($product->id())->toBe($productId);
});

it('creates a product without description', function () {
    $repository = new InMemoryProductRepository();
    $handler = new CreateProductCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $productId = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
    ));

    $product = $repository->findById($productId);

    expect($product)->not->toBeNull()
        ->and($product->description())->toBeNull();
});

it('creates a product with initial stock', function () {
    $repository = new InMemoryProductRepository();
    $handler = new CreateProductCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $productId = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        stock: 15,
    ));

    $product = $repository->findById($productId);

    expect($product)->not->toBeNull()
        ->and($product->stock()->value())->toBe(15);
});

it('creates a product with an image', function () {
    $repository = new InMemoryProductRepository();
    $handler = new CreateProductCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $productId = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        imageUrl: '/media/products/nuvora-tee.svg',
    ));

    $product = $repository->findById($productId);

    expect($product)->not->toBeNull()
        ->and($product->image()?->value())->toBe('/media/products/nuvora-tee.svg');
});
