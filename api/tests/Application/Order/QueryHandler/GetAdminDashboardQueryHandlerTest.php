<?php

declare(strict_types=1);

use App\Order\Application\Query\GetAdminDashboardQuery;
use App\Order\Application\QueryHandler\GetAdminDashboardQueryHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;

function adminDashboardSaveOrder(
    InMemoryOrderRepository $orders,
    string $id,
    DateTimeImmutable $createdAt,
    string $status = 'pending',
    int $priceCents = 1999,
): void {
    $order = Order::place(
        OrderId::fromString($id),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Tee'),
            UnitPrice::fromCents($priceCents),
            Quantity::fromInt(1),
        )],
        $createdAt,
        samplePostalAddress(),
        samplePostalAddress(),
        sampleShippingMethod(),
    );

    if ($status === 'paid') {
        $order->markPaid();
    }

    if ($status === 'cancelled') {
        $order->cancel();
    }

    $orders->save($order);
}

it('summarises paid revenue, today\'s orders and low stock', function () {
    $orders = new InMemoryOrderRepository();
    $products = new InMemoryProductRepository();
    $now = new DateTimeImmutable('2026-10-27T12:00:00+00:00');

    adminDashboardSaveOrder($orders, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', new DateTimeImmutable('2026-10-26T15:00:00+00:00'), 'paid', 3000);
    adminDashboardSaveOrder($orders, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', new DateTimeImmutable('2026-10-27T09:00:00+00:00'), 'paid', 1500);
    adminDashboardSaveOrder($orders, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', new DateTimeImmutable('2026-10-27T10:00:00+00:00'), 'pending', 8000);
    adminDashboardSaveOrder($orders, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa4', new DateTimeImmutable('2026-10-27T11:00:00+00:00'), 'cancelled', 400);

    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Mug', $now, 1299, null, 0);
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440002', 'Tee', $now, 1999, null, 2);
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440003', 'Hoodie', $now, 4999, null, 10);
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440004', 'Draft', $now, 999, null, 1);
    $draft = $products->findById(\App\Product\Domain\ValueObject\ProductId::fromString('550e8400-e29b-41d4-a716-446655440004'));
    expect($draft)->not->toBeNull();
    $draft->changePublication(false);
    $products->save($draft);

    $response = (new GetAdminDashboardQueryHandler($orders, $products, new FixedClock($now)))
        ->handle(new GetAdminDashboardQuery());

    expect($response->toArray())->toEqual([
        'revenue' => ['cents' => 4500, 'currency' => 'EUR'],
        'today' => [
            'orders' => 3,
            'revenue' => ['cents' => 1500, 'currency' => 'EUR'],
        ],
        'lowStock' => [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'name' => 'Mug',
                'slug' => 'mug',
                'stock' => 0,
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'name' => 'Tee',
                'slug' => 'tee',
                'stock' => 2,
            ],
        ],
    ]);
});

it('returns zeros when the shop is empty', function () {
    $response = (new GetAdminDashboardQueryHandler(
        new InMemoryOrderRepository(),
        new InMemoryProductRepository(),
        new FixedClock(new DateTimeImmutable('2026-10-27T12:00:00+00:00')),
    ))->handle(new GetAdminDashboardQuery());

    expect($response->toArray())->toEqual([
        'revenue' => ['cents' => 0, 'currency' => 'EUR'],
        'today' => [
            'orders' => 0,
            'revenue' => ['cents' => 0, 'currency' => 'EUR'],
        ],
        'lowStock' => [],
    ]);
});
