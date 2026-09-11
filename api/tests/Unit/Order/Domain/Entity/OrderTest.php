<?php

declare(strict_types=1);

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\EmptyOrder;
use App\Order\Domain\Exception\InvalidOrderTransition;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;

function orderLine(
    string $productId = '550e8400-e29b-41d4-a716-446655440000',
    string $name = 'Nuvora Tee',
    int $unitPriceCents = 1999,
    int $quantity = 1,
): OrderItem {
    return OrderItem::of(
        CatalogProductId::fromString($productId),
        OrderedProductName::fromString($name),
        UnitPrice::fromCents($unitPriceCents),
        Quantity::fromInt($quantity),
    );
}

it('places an order as pending with a customer and line snapshots', function () {
    $id = OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    $customerId = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $item = orderLine(quantity: 2);

    $order = Order::place($id, $customerId, [$item], $createdAt);

    expect($order->id())->toBe($id)
        ->and($order->customerId())->toBe($customerId)
        ->and($order->status())->toEqual(OrderStatus::pending())
        ->and($order->createdAt())->toBe($createdAt)
        ->and($order->items())->toBe([$item])
        ->and($order->totalCents())->toBe(3998);
});

it('sums line totals across items', function () {
    $order = Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            orderLine(unitPriceCents: 1999, quantity: 2),
            orderLine(
                productId: '550e8400-e29b-41d4-a716-446655440001',
                name: 'Nuvora Hoodie',
                unitPriceCents: 4999,
                quantity: 1,
            ),
        ],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    expect($order->totalCents())->toBe(8997);
});

it('rejects an order without items', function () {
    Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
})->throws(EmptyOrder::class);

it('cancels a pending order', function () {
    $order = Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [orderLine()],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $order->cancel();

    expect($order->status())->toEqual(OrderStatus::cancelled());
});

it('marks a pending order as paid', function () {
    $order = Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [orderLine()],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $order->markPaid();

    expect($order->status())->toEqual(OrderStatus::paid());
});

it('rejects cancelling a paid order', function () {
    $order = Order::reconstitute(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [orderLine()],
        OrderStatus::paid(),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $order->cancel();
})->throws(InvalidOrderTransition::class);

it('rejects marking a cancelled order as paid', function () {
    $order = Order::reconstitute(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [orderLine()],
        OrderStatus::cancelled(),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $order->markPaid();
})->throws(InvalidOrderTransition::class);

it('reconstitutes an order with a persisted status', function () {
    $order = Order::reconstitute(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [orderLine()],
        OrderStatus::paid(),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    expect($order->status())->toEqual(OrderStatus::paid());
});
