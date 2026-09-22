<?php

declare(strict_types=1);

use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Application\Command\CheckoutCartCommand;
use App\Cart\Application\CommandHandler\AddToCartCommandHandler;
use App\Cart\Application\CommandHandler\CheckoutCartCommandHandler;
use App\Cart\Application\Response\CatalogSnapshot as CartCatalogSnapshot;
use App\Cart\Domain\Exception\CartAlreadyCheckingOut;
use App\Cart\Domain\Exception\EmptyCart;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\Order\Application\Response\CatalogSnapshot as OrderCatalogSnapshot;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Tests\Doubles\FakeCartCatalog;
use App\Tests\Doubles\FakeCatalog;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\RecordingEventDispatcher;

it('checks out the cart into an order and clears the cart', function () {
    $carts = new InMemoryCartRepository();
    $orders = new InMemoryOrderRepository();
    $cartCatalog = new FakeCartCatalog();
    $orderCatalog = new FakeCatalog();
    $productId = '550e8400-e29b-41d4-a716-446655440000';
    $cartCatalog->add(new CartCatalogSnapshot($productId, 'Nuvora Tee', 1999, 10));
    $orderCatalog->add(new OrderCatalogSnapshot($productId, 'Nuvora Tee', 1999, 10));
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    (new AddToCartCommandHandler($carts, $cartCatalog, new FixedClock($now)))->handle(new AddToCartCommand(
        customerId: '11111111-1111-4111-8111-111111111111',
        productId: $productId,
        quantity: 2,
    ));

    $orderId = (new CheckoutCartCommandHandler(
        $carts,
        new PlaceOrderCommandHandler(
            $orders,
            $orderCatalog,
            new FixedClock($now),
            new RecordingEventDispatcher(),
        ),
        new FixedClock($now),
    ))->handle(new CheckoutCartCommand('11111111-1111-4111-8111-111111111111', sampleOrderAddress(), sampleOrderAddress(), 'standard'));

    $order = $orders->findById($orderId);
    $cart = $carts->findByCustomerId(CustomerId::fromString('11111111-1111-4111-8111-111111111111'));

    expect($order)->not->toBeNull()
        ->and($order->items())->toHaveCount(1)
        ->and($order->items()[0]->quantity()->value())->toBe(2)
        ->and($order->shipping()?->feeCents())->toBe(490)
        ->and($order->totalCents())->toBe(4488)
        ->and($order->shippingAddress()?->city())->toBe('Paris')
        ->and($order->billingAddress()?->country())->toBe('FR')
        ->and($cart?->isEmpty())->toBeTrue()
        ->and($orderCatalog->findById(CatalogProductId::fromString($productId))?->stock)->toBe(8);
});

it('rejects checking out an empty cart', function () {
    (new CheckoutCartCommandHandler(
        new InMemoryCartRepository(),
        new PlaceOrderCommandHandler(
            new InMemoryOrderRepository(),
            new FakeCatalog(),
            new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
            new RecordingEventDispatcher(),
        ),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new CheckoutCartCommand('11111111-1111-4111-8111-111111111111', sampleOrderAddress(), sampleOrderAddress(), 'standard'));
})->throws(EmptyCart::class);

it('does not place a second order when another checkout already claimed the cart', function () {
    $carts = new InMemoryCartRepository();
    $orders = new InMemoryOrderRepository();
    $cartCatalog = new FakeCartCatalog();
    $orderCatalog = new FakeCatalog();
    $productId = '550e8400-e29b-41d4-a716-446655440000';
    $customerId = '11111111-1111-4111-8111-111111111111';
    $cartCatalog->add(new CartCatalogSnapshot($productId, 'Nuvora Tee', 1999, 10));
    $orderCatalog->add(new OrderCatalogSnapshot($productId, 'Nuvora Tee', 1999, 10));
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    (new AddToCartCommandHandler($carts, $cartCatalog, new FixedClock($now)))->handle(new AddToCartCommand(
        customerId: $customerId,
        productId: $productId,
        quantity: 2,
    ));

    $loaded = $carts->findByCustomerId(CustomerId::fromString($customerId));
    $carts->claimForCheckout($loaded);

    $handler = new CheckoutCartCommandHandler(
        $carts,
        new PlaceOrderCommandHandler(
            $orders,
            $orderCatalog,
            new FixedClock($now),
            new RecordingEventDispatcher(),
        ),
        new FixedClock($now),
    );

    expect(fn () => $handler->handle(new CheckoutCartCommand(
        $customerId,
        sampleOrderAddress(),
        sampleOrderAddress(),
        'standard',
    )))->toThrow(CartAlreadyCheckingOut::class)
        ->and($orders->all())->toBe([])
        ->and($orderCatalog->findById(CatalogProductId::fromString($productId))?->stock)->toBe(10);
});
