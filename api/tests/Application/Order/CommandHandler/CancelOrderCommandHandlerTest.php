<?php

declare(strict_types=1);

use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Application\CommandHandler\CancelOrderCommandHandler;
use App\Order\Application\Response\CatalogSnapshot;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\InvalidOrderTransition;
use App\Order\Domain\Exception\OrderAccessForbidden;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Shared\Application\Event\OrderCancelled;
use App\Tests\Doubles\FakeCatalog;
use App\Tests\Doubles\RecordingEventDispatcher;

function cancelOrderSeed(
    InMemoryOrderRepository $repository,
    string $orderId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    string $customerId = '11111111-1111-4111-8111-111111111111',
    ?OrderStatus $status = null,
): Order {
    $order = $status === null
        ? Order::place(
            OrderId::fromString($orderId),
            CustomerId::fromString($customerId),
            [OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Nuvora Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            )],
            new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        )
        : Order::reconstitute(
            OrderId::fromString($orderId),
            CustomerId::fromString($customerId),
            [OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Nuvora Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            )],
            $status,
            new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        );

    $repository->save($order);

    return $order;
}

function cancelOrderCatalog(int $stock = 0): FakeCatalog
{
    $catalog = new FakeCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: $stock,
    ));

    return $catalog;
}

it('cancels a pending order owned by the customer', function () {
    $repository = new InMemoryOrderRepository();
    $catalog = cancelOrderCatalog(stock: 4);
    $events = new RecordingEventDispatcher();
    cancelOrderSeed($repository);

    (new CancelOrderCommandHandler($repository, $catalog, $events))->handle(new CancelOrderCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));

    $order = $repository->findById(OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($order)->not->toBeNull()
        ->and($order->status())->toEqual(OrderStatus::cancelled())
        ->and($catalog->findById(CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'))?->stock)->toBe(5)
        ->and($events->dispatched)->toHaveCount(1)
        ->and($events->dispatched[0])->toBeInstanceOf(OrderCancelled::class)
        ->and($events->dispatched[0]->orderId)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
});

it('rejects cancelling another customer order', function () {
    $repository = new InMemoryOrderRepository();
    cancelOrderSeed($repository);

    (new CancelOrderCommandHandler($repository, cancelOrderCatalog(), new RecordingEventDispatcher()))->handle(new CancelOrderCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '22222222-2222-4222-8222-222222222222',
    ));
})->throws(OrderAccessForbidden::class);

it('rejects a missing order', function () {
    (new CancelOrderCommandHandler(new InMemoryOrderRepository(), cancelOrderCatalog(), new RecordingEventDispatcher()))->handle(new CancelOrderCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));
})->throws(OrderNotFound::class);

it('rejects cancelling a paid order', function () {
    $repository = new InMemoryOrderRepository();
    cancelOrderSeed($repository, status: OrderStatus::paid());

    (new CancelOrderCommandHandler($repository, cancelOrderCatalog(), new RecordingEventDispatcher()))->handle(new CancelOrderCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));
})->throws(InvalidOrderTransition::class);
