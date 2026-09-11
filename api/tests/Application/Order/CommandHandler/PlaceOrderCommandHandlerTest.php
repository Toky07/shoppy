<?php

declare(strict_types=1);

use App\Order\Application\Command\PlaceOrderCommand;
use App\Order\Application\Command\PlaceOrderLine;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\Exception\CatalogProductNotFound;
use App\Order\Domain\Exception\EmptyOrder;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Product\Domain\Exception\InsufficientProductStock;
use App\Shared\Application\Event\OrderPlaced;
use App\Tests\Doubles\FakeCatalog;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\RecordingEventDispatcher;

it('places an order with catalog snapshots, ignoring later catalog prices', function () {
    $orders = new InMemoryOrderRepository();
    $catalog = new FakeCatalog();
    $events = new RecordingEventDispatcher();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
    ));
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = new PlaceOrderCommandHandler($orders, $catalog, new FixedClock($createdAt), $events);

    $orderId = $handler->handle(new PlaceOrderCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        items: [new PlaceOrderLine(
            productId: '550e8400-e29b-41d4-a716-446655440000',
            quantity: 2,
        )],
    ));

    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Renamed Tee',
        unitPriceCents: 9999,
        stock: 99,
    ));

    $order = $orders->findById($orderId);

    expect($order)->not->toBeNull()
        ->and($order->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($order->status())->toEqual(OrderStatus::pending())
        ->and($order->createdAt())->toBe($createdAt)
        ->and($order->items())->toHaveCount(1)
        ->and($order->items()[0]->catalogProductId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($order->items()[0]->name()->value())->toBe('Nuvora Tee')
        ->and($order->items()[0]->unitPrice()->cents())->toBe(1999)
        ->and($order->items()[0]->quantity()->value())->toBe(2)
        ->and($order->totalCents())->toBe(3998)
        ->and($events->dispatched)->toHaveCount(1)
        ->and($events->dispatched[0])->toBeInstanceOf(OrderPlaced::class)
        ->and($events->dispatched[0]->orderId)->toBe($orderId->value())
        ->and($events->dispatched[0]->amountCents)->toBe(3998);
});

it('decrements catalog stock when placing an order', function () {
    $orders = new InMemoryOrderRepository();
    $catalog = new FakeCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 5,
    ));

    (new PlaceOrderCommandHandler(
        $orders,
        $catalog,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        new RecordingEventDispatcher(),
    ))->handle(new PlaceOrderCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        items: [new PlaceOrderLine(
            productId: '550e8400-e29b-41d4-a716-446655440000',
            quantity: 2,
        )],
    ));

    $snapshot = $catalog->findById(CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'));

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->stock)->toBe(3)
        ->and($orders->all())->toHaveCount(1);
});

it('rejects insufficient stock without persisting the order', function () {
    $orders = new InMemoryOrderRepository();
    $catalog = new FakeCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 1,
    ));

    expect(fn () => (new PlaceOrderCommandHandler(
        $orders,
        $catalog,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        new RecordingEventDispatcher(),
    ))->handle(new PlaceOrderCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        items: [new PlaceOrderLine(
            productId: '550e8400-e29b-41d4-a716-446655440000',
            quantity: 2,
        )],
    )))->toThrow(InsufficientProductStock::class)
        ->and($orders->all())->toBe([])
        ->and($catalog->findById(CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'))?->stock)->toBe(1);
});

it('rejects a missing catalog product without persisting', function () {
    $orders = new InMemoryOrderRepository();
    $handler = new PlaceOrderCommandHandler(
        $orders,
        new FakeCatalog(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        new RecordingEventDispatcher(),
    );

    expect(fn () => $handler->handle(new PlaceOrderCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        items: [new PlaceOrderLine(
            productId: '550e8400-e29b-41d4-a716-446655440000',
            quantity: 1,
        )],
    )))->toThrow(CatalogProductNotFound::class)
        ->and($orders->all())->toBe([]);
});

it('rejects an order without items', function () {
    $handler = new PlaceOrderCommandHandler(
        new InMemoryOrderRepository(),
        new FakeCatalog(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        new RecordingEventDispatcher(),
    );

    $handler->handle(new PlaceOrderCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        items: [],
    ));
})->throws(EmptyOrder::class);
