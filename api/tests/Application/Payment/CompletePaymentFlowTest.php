<?php

declare(strict_types=1);

use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Application\EventSubscriber\MarkOrderPaidOnPaymentCompleted;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\OrderStatus;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Payment\Application\Command\CompletePaymentCommand;
use App\Payment\Application\CommandHandler\CompletePaymentCommandHandler;
use App\Payment\Application\EventSubscriber\CancelPaymentOnOrderCancelled;
use App\Payment\Application\EventSubscriber\CreatePaymentOnOrderPlaced;
use App\Payment\Domain\Exception\PaymentAlreadyCompleted;
use App\Payment\Domain\Exception\PaymentChargeFailed;
use App\Payment\Domain\Exception\PaymentNotFound;
use App\Payment\Domain\Exception\PaymentNotPayable;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Payment\Infrastructure\Persistence\InMemoryPaymentRepository;
use App\Shared\Application\Event\OrderCancelled;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\FakePaymentGateway;
use App\Tests\Doubles\FixedClock;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('creates a pending payment when an order is placed', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced(
        $payments,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ));

    $dispatcher->dispatch(new OrderPlaced(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 3998,
    ));

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($payment)->not->toBeNull()
        ->and($payment->status())->toEqual(PaymentStatus::pending())
        ->and($payment->amount()->cents())->toBe(3998)
        ->and($payment->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111');
});

it('completes a payment and marks the order paid through events', function () {
    $payments = new InMemoryPaymentRepository();
    $orders = new InMemoryOrderRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $orderId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    $customerId = '11111111-1111-4111-8111-111111111111';

    $orders->save(Order::place(
        OrderId::fromString($orderId),
        CustomerId::fromString($customerId),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(2),
        )],
        $now,
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod()));

    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->addSubscriber(new MarkOrderPaidOnPaymentCompleted(
        $orders,
        new MarkOrderPaidCommandHandler($orders),
    ));

    $dispatcher->dispatch(new OrderPlaced($orderId, $customerId, 3998));

    (new CompletePaymentCommandHandler($payments, new LocalPaymentGateway(), new FixedClock($now), $dispatcher))
        ->handle(new CompletePaymentCommand($orderId, $customerId));

    $payment = $payments->findByOrderId(OrderReference::fromString($orderId));
    $order = $orders->findById(OrderId::fromString($orderId));

    expect($payment?->status())->toEqual(PaymentStatus::completed())
        ->and($order?->status())->toEqual(OrderStatus::paid())
        ->and($payment?->completedAt())->toBe($now);
});

it('cancels a pending payment when the order is cancelled', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->addSubscriber(new CancelPaymentOnOrderCancelled($payments));

    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));
    $dispatcher->dispatch(new OrderCancelled(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($payment?->status())->toEqual(PaymentStatus::cancelled());
});

it('rejects paying a cancelled payment', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->addSubscriber(new CancelPaymentOnOrderCancelled($payments));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));
    $dispatcher->dispatch(new OrderCancelled(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));

    (new CompletePaymentCommandHandler($payments, new LocalPaymentGateway(), new FixedClock($now), $dispatcher))
        ->handle(new CompletePaymentCommand(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            '11111111-1111-4111-8111-111111111111',
        ));
})->throws(PaymentNotPayable::class);

it('rejects a failed gateway charge', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    (new CompletePaymentCommandHandler(
        $payments,
        new FakePaymentGateway(succeeds: false),
        new FixedClock($now),
        $dispatcher,
    ))->handle(new CompletePaymentCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));
})->throws(PaymentChargeFailed::class);

it('rejects completing an unknown payment', function () {
    (new CompletePaymentCommandHandler(
        new InMemoryPaymentRepository(),
        new LocalPaymentGateway(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        new EventDispatcher(),
    ))->handle(new CompletePaymentCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));
})->throws(PaymentNotFound::class);

it('rejects completing an already completed payment', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    $handler = new CompletePaymentCommandHandler($payments, new LocalPaymentGateway(), new FixedClock($now), $dispatcher);
    $handler->handle(new CompletePaymentCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));

    $handler->handle(new CompletePaymentCommand(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
    ));
})->throws(PaymentAlreadyCompleted::class);

it('dispatches payment completed when paying', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $recorded = [];
    $dispatcher->addListener(PaymentCompleted::class, static function (PaymentCompleted $event) use (&$recorded): void {
        $recorded[] = $event;
    });
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    (new CompletePaymentCommandHandler($payments, new LocalPaymentGateway(), new FixedClock($now), $dispatcher))
        ->handle(new CompletePaymentCommand(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            '11111111-1111-4111-8111-111111111111',
        ));

    expect($recorded)->toHaveCount(1)
        ->and($recorded[0]->orderId)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($recorded[0]->amountCents)->toBe(3998);
});
