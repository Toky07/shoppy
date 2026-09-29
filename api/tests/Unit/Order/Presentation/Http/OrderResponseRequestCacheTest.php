<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\Unauthenticated;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Order\Presentation\Http\OrderResponseRequestCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

it('loads an order only once per request', function () {
    $repository = new InMemoryOrderRepository();
    $orderId = OrderId::fromString('11111111-1111-4111-8111-111111111111');
    $repository->save(Order::place(
        $orderId,
        CustomerId::fromString('22222222-2222-4222-8222-222222222222'),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(1),
        )],
        new DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        samplePostalAddress(),
        samplePostalAddress(),
        sampleShippingMethod(),
    ));

    $cache = new OrderResponseRequestCache(new GetOrderQueryHandler($repository), new TokenStorage());
    $request = Request::create('/orders/'.$orderId->value());

    $first = $cache->get($request, $orderId->value());
    $second = $cache->get($request, $orderId->value());

    expect($first->id)->toBe($orderId->value())
        ->and($second)->toBe($first);
});

it('maps a missing order to unauthenticated for anonymous callers', function () {
    $cache = new OrderResponseRequestCache(
        new GetOrderQueryHandler(new InMemoryOrderRepository()),
        new TokenStorage(),
    );

    $cache->get(
        Request::create('/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    );
})->throws(Unauthenticated::class);
