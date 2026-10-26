<?php

declare(strict_types=1);

use App\Cart\Application\AvailableStock;
use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Application\Command\MergeCartCommand;
use App\Cart\Application\Command\MergeCartLine;
use App\Cart\Application\CommandHandler\AddToCartCommandHandler;
use App\Cart\Application\CommandHandler\MergeCartCommandHandler;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Tests\Doubles\CountingCartRepository;
use App\Tests\Doubles\FakeCartCatalog;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\ImmediateTransactionRunner;
use App\Tests\Doubles\RecordingCartCatalog;
use App\Tests\Doubles\RecordingEventDispatcher;

function mergeCartHandler(
    CountingCartRepository $carts,
    RecordingCartCatalog $catalog,
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): MergeCartCommandHandler {
    return new MergeCartCommandHandler(
        $catalog,
        new AvailableStock($carts, $catalog),
        $carts,
        new ImmediateTransactionRunner(new RecordingEventDispatcher()),
        new FixedClock($now),
    );
}

it('merges several guest lines in one catalog and reservation pass and one save', function () {
    $inner = new InMemoryCartRepository();
    $carts = new CountingCartRepository($inner);
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
    ));
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440001',
        name: 'Nuvora Mug',
        unitPriceCents: 1299,
        stock: 5,
    ));
    $recording = new RecordingCartCatalog($catalog);
    $customerId = '11111111-1111-4111-8111-111111111111';

    mergeCartHandler($carts, $recording)->handle(new MergeCartCommand($customerId, [
        new MergeCartLine('550e8400-e29b-41d4-a716-446655440000', 2),
        new MergeCartLine('550e8400-e29b-41d4-a716-446655440001', 1),
        new MergeCartLine('00000000-0000-4000-8000-000000000000', 4),
    ]));

    $cart = $inner->findByCustomerId(CustomerId::fromString($customerId));

    expect($recording->findByItemsCalls)->toBe(1)
        ->and($recording->findByIdCalls)->toBe(0)
        ->and($carts->reservedQuantitiesCalls)->toBe(1)
        ->and($carts->reservedQuantityCalls)->toBe(0)
        ->and($carts->findByCustomerIdCalls)->toBe(1)
        ->and($carts->saveCalls)->toBe(1)
        ->and($cart?->items())->toHaveCount(2)
        ->and($cart?->items()[0]->quantity()->value())->toBe(2)
        ->and($cart?->items()[1]->quantity()->value())->toBe(1);
});

it('caps guest quantities to remaining stock including the customer cart', function () {
    $inner = new InMemoryCartRepository();
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 5,
    ));
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $customerId = '11111111-1111-4111-8111-111111111111';

    (new AddToCartCommandHandler(
        $inner,
        $catalog,
        new AvailableStock($inner, $catalog),
        new FixedClock($now),
    ))->handle(new AddToCartCommand(
        customerId: $customerId,
        productId: '550e8400-e29b-41d4-a716-446655440000',
        quantity: 2,
    ));

    $carts = new CountingCartRepository($inner);
    $recording = new RecordingCartCatalog($catalog);

    mergeCartHandler($carts, $recording, $now)->handle(new MergeCartCommand($customerId, [
        new MergeCartLine('550e8400-e29b-41d4-a716-446655440000', 10),
    ]));

    $cart = $inner->findByCustomerId(CustomerId::fromString($customerId));

    expect($cart?->items())->toHaveCount(1)
        ->and($cart?->items()[0]->quantity()->value())->toBe(5)
        ->and($carts->saveCalls)->toBe(1);
});

it('skips a variant-required product when the guest line has no variant', function () {
    $inner = new InMemoryCartRepository();
    $carts = new CountingCartRepository($inner);
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
        requiresVariant: true,
    ));
    $customerId = '11111111-1111-4111-8111-111111111111';

    mergeCartHandler($carts, new RecordingCartCatalog($catalog))->handle(new MergeCartCommand($customerId, [
        new MergeCartLine('550e8400-e29b-41d4-a716-446655440000', 1),
    ]));

    expect($inner->findByCustomerId(CustomerId::fromString($customerId)))->toBeNull()
        ->and($carts->saveCalls)->toBe(0);
});
