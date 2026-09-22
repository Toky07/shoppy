<?php

declare(strict_types=1);

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use Doctrine\ORM\EntityManagerInterface;

it('persists an order and its line snapshots', function () {
    $id = OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $order = Order::place(
        $id,
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Nuvora Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(2),
            ),
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                OrderedProductName::fromString('Nuvora Hoodie'),
                UnitPrice::fromCents(4999),
                Quantity::fromInt(1),
            ),
        ],
        $createdAt,
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod());

    $repository = self::getContainer()->get(OrderRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($order);
    $entityManager->clear();

    $found = $repository->findById($id);

    expect($found)->not->toBeNull()
        ->and($found->id()->value())->toBe($id->value())
        ->and($found->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($found->status())->toEqual(OrderStatus::pending())
        ->and($found->createdAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:00:00+00:00')
        ->and($found->items())->toHaveCount(2)
        ->and($found->items()[0]->catalogProductId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($found->items()[0]->name()->value())->toBe('Nuvora Tee')
        ->and($found->items()[0]->unitPrice()->cents())->toBe(1999)
        ->and($found->items()[0]->unitPrice()->currency())->toBe('EUR')
        ->and($found->items()[0]->quantity()->value())->toBe(2)
        ->and($found->items()[1]->name()->value())->toBe('Nuvora Hoodie')
        ->and($found->shipping()?->code())->toBe('standard')
        ->and($found->shipping()?->feeCents())->toBe(0)
        ->and($found->totalCents())->toBe(8997);
});

it('returns null when the order does not exist', function () {
    $repository = self::getContainer()->get(OrderRepository::class);

    expect($repository->findById(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
    ))->toBeNull();
});

it('persists and reloads a cancelled order status', function () {
    $id = OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    $order = Order::place(
        $id,
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Nuvora Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod());

    $repository = self::getContainer()->get(OrderRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($order);
    $entityManager->clear();

    $loaded = $repository->findById($id);
    expect($loaded)->not->toBeNull();
    $loaded->cancel();
    $repository->save($loaded);
    $entityManager->clear();

    $found = $repository->findById($id);

    expect($found)->not->toBeNull()
        ->and($found->status())->toEqual(OrderStatus::cancelled());
});

it('lists a customer orders newest first', function () {
    $repository = self::getContainer()->get(OrderRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Older Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));
    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                OrderedProductName::fromString('Newer Tee'),
                UnitPrice::fromCents(2999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));
    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3'),
        CustomerId::fromString('22222222-2222-4222-8222-222222222222'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440002'),
                OrderedProductName::fromString('Other Customer Tee'),
                UnitPrice::fromCents(3999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-21T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));

    $entityManager->clear();

    $orders = $repository->findPageByCustomer(
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        0,
        20,
    );

    expect($repository->countByCustomer(
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
    ))->toBe(2)
        ->and($orders)->toHaveCount(2)
        ->and($orders[0]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($orders[1]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('lists all orders newest first across customers', function () {
    $repository = self::getContainer()->get(OrderRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
                OrderedProductName::fromString('Older Tee'),
                UnitPrice::fromCents(1999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));
    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2'),
        CustomerId::fromString('22222222-2222-4222-8222-222222222222'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
                OrderedProductName::fromString('Other Tee'),
                UnitPrice::fromCents(2999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));
    $repository->save(Order::place(
        OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [
            OrderItem::of(
                CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440002'),
                OrderedProductName::fromString('Newest Tee'),
                UnitPrice::fromCents(3999),
                Quantity::fromInt(1),
            ),
        ],
        new DateTimeImmutable('2026-08-21T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));

    $entityManager->clear();

    $orders = $repository->findPage(0, 20);

    expect($repository->countAll())->toBe(3)
        ->and($orders)->toHaveCount(3)
        ->and($orders[0]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3')
        ->and($orders[1]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($orders[2]->id()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});
