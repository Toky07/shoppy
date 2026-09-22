<?php

declare(strict_types=1);

use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Exception\OrderNotFound;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;

it('returns an order by id', function () {
    $repository = new InMemoryOrderRepository();
    $id = OrderId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $repository->save(Order::place(
        $id,
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(2),
        )],
        $createdAt,
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));

    $response = (new GetOrderQueryHandler($repository))->handle(
        new GetOrderQuery($id->value()),
    );

    expect($response->toArray())->toBe([
        'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'customerId' => '11111111-1111-4111-8111-111111111111',
        'status' => 'pending',
        'items' => [
            [
                'productId' => '550e8400-e29b-41d4-a716-446655440000',
                'name' => 'Nuvora Tee',
                'quantity' => 2,
                'unitPrice' => [
                    'cents' => 1999,
                    'currency' => 'EUR',
                ],
                'lineTotal' => [
                    'cents' => 3998,
                    'currency' => 'EUR',
                ],
            ],
        ],
        'total' => [
            'cents' => 3998,
            'currency' => 'EUR',
        ],
        'createdAt' => '2026-08-20T12:00:00+00:00',
        'shippingAddress' => [
            'recipient' => 'Ada Lovelace',
            'line1' => '10 rue de la Paix',
            'line2' => null,
            'postalCode' => '75002',
            'city' => 'Paris',
            'country' => 'FR',
        ],
        'billingAddress' => [
            'recipient' => 'Ada Lovelace',
            'line1' => '10 rue de la Paix',
            'line2' => null,
            'postalCode' => '75002',
            'city' => 'Paris',
            'country' => 'FR',
        ],
        'shipping' => [
            'method' => 'standard',
            'label' => 'Standard',
            'fee' => [
                'cents' => 0,
                'currency' => 'EUR',
            ],
        ],
    ]);
});

it('fails when the order does not exist', function () {
    $handler = new GetOrderQueryHandler(new InMemoryOrderRepository());

    $handler->handle(new GetOrderQuery('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
})->throws(OrderNotFound::class);
