<?php

declare(strict_types=1);

use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Application\CommandHandler\AddToCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\Exception\CartProductNotFound;
use App\Cart\Domain\Exception\InsufficientCartStock;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Tests\Doubles\FakeCartCatalog;
use App\Tests\Doubles\FixedClock;

it('adds a product to the cart and enriches the response from the catalog', function () {
    $carts = new InMemoryCartRepository();
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
    ));
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    (new AddToCartCommandHandler($carts, $catalog, new FixedClock($now)))->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 2,
    ));

    $response = (new GetCartQueryHandler($carts, $catalog))->handle(new GetCartQuery(
        '11111111-1111-4111-8111-111111111111',
    ));

    expect($response->toArray())->toMatchArray([
        'customerId' => '11111111-1111-4111-8111-111111111111',
        'total' => ['cents' => 3998, 'currency' => 'EUR'],
    ])
        ->and($response->id)->not->toBeNull()
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->toArray())->toBe([
            'productId' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Nuvora Tee',
            'quantity' => 2,
            'unitPrice' => ['cents' => 1999, 'currency' => 'EUR'],
            'lineTotal' => ['cents' => 3998, 'currency' => 'EUR'],
            'availableStock' => 10,
        ])
        ->and($carts->findByCustomerId(CustomerId::fromString('11111111-1111-4111-8111-111111111111'))?->items()[0]->quantity()->value())->toBe(2);
});

it('merges quantities when adding the same product', function () {
    $carts = new InMemoryCartRepository();
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
    ));
    $handler = new AddToCartCommandHandler(
        $carts,
        $catalog,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $handler->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 2,
    ));
    $handler->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 3,
    ));

    $cart = $carts->findByCustomerId(CustomerId::fromString('11111111-1111-4111-8111-111111111111'));

    expect($cart?->items())->toHaveCount(1)
        ->and($cart?->items()[0]->quantity()->value())->toBe(5);
});

it('rejects an unknown catalog product', function () {
    (new AddToCartCommandHandler(
        new InMemoryCartRepository(),
        new FakeCartCatalog(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 1,
    ));
})->throws(CartProductNotFound::class);

it('rejects adding more than available stock', function () {
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 2,
    ));

    (new AddToCartCommandHandler(
        new InMemoryCartRepository(),
        $catalog,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 3,
    ));
})->throws(InsufficientCartStock::class);

it('returns an empty cart when none exists', function () {
    $response = (new GetCartQueryHandler(new InMemoryCartRepository(), new FakeCartCatalog()))
        ->handle(new GetCartQuery('11111111-1111-4111-8111-111111111111'));

    expect($response->toArray())->toBe([
        'id' => null,
        'customerId' => '11111111-1111-4111-8111-111111111111',
        'items' => [],
        'total' => ['cents' => 0, 'currency' => 'EUR'],
        'updatedAt' => null,
    ]);
});
