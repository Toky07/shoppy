<?php

declare(strict_types=1);

use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\InvalidOrderTransition;
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

function markPaidOrderSeed(
    InMemoryOrderRepository $repository,
    ?OrderStatus $status = null,
): void {
    $order = $status === null
        ? Order::place(
            OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
            [OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Nuvora Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            )],
            new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        samplePostalAddress(), samplePostalAddress(), sampleShippingMethod())
        : Order::reconstitute(
            OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
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
}

it('marks a pending order as paid', function () {
    $repository = new InMemoryOrderRepository();
    markPaidOrderSeed($repository);

    (new MarkOrderPaidCommandHandler($repository))->handle(new MarkOrderPaidCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    ));

    $order = $repository->findById(OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($order)->not->toBeNull()
        ->and($order->status())->toEqual(OrderStatus::paid());
});

it('rejects a missing order', function () {
    (new MarkOrderPaidCommandHandler(new InMemoryOrderRepository()))->handle(new MarkOrderPaidCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    ));
})->throws(OrderNotFound::class);

it('rejects marking a cancelled order as paid', function () {
    $repository = new InMemoryOrderRepository();
    markPaidOrderSeed($repository, OrderStatus::cancelled());

    (new MarkOrderPaidCommandHandler($repository))->handle(new MarkOrderPaidCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    ));
})->throws(InvalidOrderTransition::class);
