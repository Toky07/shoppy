<?php

declare(strict_types=1);

use App\Address\Application\EventSubscriber\RememberAddressOnOrderPlaced;
use App\Address\Domain\ValueObject\CustomerId as AddressCustomerId;
use App\Address\Infrastructure\Persistence\InMemoryAddressBookRepository;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\PostalAddress;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Shared\Application\Event\OrderPlaced;

function placedOrder(PostalAddress $shipping, PostalAddress $billing): array
{
    $orderId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    $customerId = '11111111-1111-4111-8111-111111111111';
    $order = Order::place(
        OrderId::fromString($orderId),
        CustomerId::fromString($customerId),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(1),
        )],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        $shipping,
        $billing,
        sampleShippingMethod(),
    );

    return [$orderId, $customerId, $order];
}

function lyonPostal(): PostalAddress
{
    return PostalAddress::fromInput(
        'Ada Lovelace',
        '2 place Bellecour',
        null,
        '69002',
        'Lyon',
        'FR',
        'address',
    );
}

it('saves the shipping address after an order is placed', function () {
    [$orderId, $customerId, $order] = placedOrder(samplePostalAddress(), samplePostalAddress());
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $books = new InMemoryAddressBookRepository();
    $subscriber = new RememberAddressOnOrderPlaced($orders, $books);

    $subscriber->onOrderPlaced(new OrderPlaced($orderId, $customerId, 1999));

    $book = $books->findByCustomerId(AddressCustomerId::fromString($customerId));

    expect($books->saveCalls)->toBe(1)
        ->and($book)->not->toBeNull()
        ->and($book->addresses())->toHaveCount(1)
        ->and($book->default()?->postal()->toArray())->toBe(samplePostalAddress()->toArray());
});

it('keeps a distinct billing address without making it the default', function () {
    $shipping = samplePostalAddress();
    $billing = lyonPostal();
    [$orderId, $customerId, $order] = placedOrder($shipping, $billing);
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $books = new InMemoryAddressBookRepository();
    $subscriber = new RememberAddressOnOrderPlaced($orders, $books);

    $subscriber->onOrderPlaced(new OrderPlaced($orderId, $customerId, 1999));

    $book = $books->findByCustomerId(AddressCustomerId::fromString($customerId));

    expect($book)->not->toBeNull()
        ->and($book->addresses())->toHaveCount(2)
        ->and($book->default()?->postal()->city())->toBe('Paris')
        ->and($book->addresses()[0]->postal()->city())->toBe('Lyon')
        ->and($book->addresses()[0]->isDefault())->toBeFalse();
});

it('does not duplicate the same shipping address on a later order', function () {
    [$orderId, $customerId, $order] = placedOrder(samplePostalAddress(), samplePostalAddress());
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $books = new InMemoryAddressBookRepository();
    $subscriber = new RememberAddressOnOrderPlaced($orders, $books);
    $event = new OrderPlaced($orderId, $customerId, 1999);

    $subscriber->onOrderPlaced($event);
    $subscriber->onOrderPlaced($event);

    $book = $books->findByCustomerId(AddressCustomerId::fromString($customerId));

    expect($book?->addresses())->toHaveCount(1);
});

it('ignores an unknown order', function () {
    $books = new InMemoryAddressBookRepository();
    $subscriber = new RememberAddressOnOrderPlaced(new InMemoryOrderRepository(), $books);

    $subscriber->onOrderPlaced(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        1999,
    ));

    expect($books->saveCalls)->toBe(0);
});
