<?php

declare(strict_types=1);

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

it('creates and persists a product', function () {
    $repository = new InMemoryProductRepository();
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = createProducts($repository, new FixedClock($createdAt));

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
        ->and($product->slug()->value())->toBe('nuvora-tee')
        ->and($product->createdAt())->toBe($createdAt)
        ->and($product->id())->toBe($productId);
});

it('creates a product without description', function () {
    $repository = new InMemoryProductRepository();
    $handler = createProducts(
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
    $handler = createProducts(
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

it('makes duplicate names unique with a numbered slug', function () {
    $repository = new InMemoryProductRepository();
    $handler = createProducts(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $first = $repository->findById($handler->handle(new CreateProductCommand(
        name: 'Coque Wave',
        priceCents: 1999,
    )));
    $second = $repository->findById($handler->handle(new CreateProductCommand(
        name: 'Coque Wave',
        priceCents: 2499,
    )));

    expect($first?->slug()->value())->toBe('coque-wave')
        ->and($first?->sku()->value())->toBe('COQUE-WAVE')
        ->and($second?->slug()->value())->toBe('coque-wave-2')
        ->and($second?->sku()->value())->toBe('COQUE-WAVE-2');
});

it('attaches size and color variants and sums their stock', function () {
    $repository = new InMemoryProductRepository();
    $handler = createProducts(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $productId = $handler->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        stock: 99,
        sku: 'NUVORA-TEE',
        variants: [
            ['id' => null, 'sku' => 'NUVORA-TEE-S', 'size' => 'S', 'color' => 'Noir', 'stock' => 1],
            ['id' => null, 'sku' => 'NUVORA-TEE-M', 'size' => 'M', 'color' => 'Noir', 'stock' => 4],
        ],
    ));

    $product = $repository->findById($productId);

    expect($product?->stock()->value())->toBe(5)
        ->and($product?->variants()[1]->sku()->value())->toBe('NUVORA-TEE-M')
        ->and($product?->variants()[1]->label())->toBe('M · Noir');
});
