<?php

declare(strict_types=1);

use App\Order\Application\Query\ListOrdersQuery;
use App\Order\Application\QueryHandler\ListOrdersQueryHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\InvalidOrderPagination;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;

function saveOrder(
    InMemoryOrderRepository $repository,
    string $orderId,
    string $customerId,
    string $name,
    DateTimeImmutable $createdAt,
): void {
    $repository->save(Order::place(
        OrderId::fromString($orderId),
        CustomerId::fromString($customerId),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString($name),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(1),
        )],
        $createdAt,
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));
}

it('lists current customer orders newest first', function () {
    $repository = new InMemoryOrderRepository();
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', '11111111-1111-4111-8111-111111111111', 'Older Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', '11111111-1111-4111-8111-111111111111', 'Newer Tee', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', '22222222-2222-4222-8222-222222222222', 'Other Tee', new DateTimeImmutable('2026-08-21T12:00:00+00:00'));

    $response = (new ListOrdersQueryHandler($repository))->handle(new ListOrdersQuery(
        customerId: '11111111-1111-4111-8111-111111111111',
    ));

    expect($response->toArray())->toMatchArray([
        'page' => 1,
        'limit' => 20,
        'total' => 2,
    ])
        ->and($response->toArray()['items'][0]['id'])->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($response->toArray()['items'][1]['id'])->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('paginates current customer orders', function () {
    $repository = new InMemoryOrderRepository();
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', '11111111-1111-4111-8111-111111111111', 'One', new DateTimeImmutable('2026-08-18T12:00:00+00:00'));
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', '11111111-1111-4111-8111-111111111111', 'Two', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveOrder($repository, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', '11111111-1111-4111-8111-111111111111', 'Three', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (new ListOrdersQueryHandler($repository))->handle(new ListOrdersQuery(
        customerId: '11111111-1111-4111-8111-111111111111',
        page: 2,
        limit: 2,
    ));

    expect($response->total)->toBe(3)
        ->and($response->page)->toBe(2)
        ->and($response->limit)->toBe(2)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('rejects an invalid page', function () {
    (new ListOrdersQueryHandler(new InMemoryOrderRepository()))->handle(new ListOrdersQuery(
        customerId: '11111111-1111-4111-8111-111111111111',
        page: 0,
    ));
})->throws(InvalidOrderPagination::class);
