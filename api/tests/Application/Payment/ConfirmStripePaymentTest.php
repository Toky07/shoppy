<?php

declare(strict_types=1);

use App\Payment\Application\Command\ConfirmPaymentCommand;
use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\CommandHandler\ConfirmPaymentCommandHandler;
use App\Payment\Application\CommandHandler\StartCheckoutCommandHandler;
use App\Payment\Application\EventSubscriber\CreatePaymentOnOrderPlaced;
use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Domain\Exception\PaymentAmountMismatch;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Payment\Infrastructure\Gateway\StripePaymentGateway;
use App\Payment\Infrastructure\Persistence\InMemoryPaymentRepository;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\FakePayerContact;
use App\Tests\Doubles\FakeStripeCheckoutClient;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\PendingPayableOrder;
use Symfony\Component\EventDispatcher\EventDispatcher;

function stripeGateways(FakeStripeCheckoutClient $stripe): PaymentGatewayRegistry
{
    return new PaymentGatewayRegistry([
        new LocalPaymentGateway(),
        new StripePaymentGateway($stripe, 'eur', new FakePayerContact()),
    ], 'local');
}

function startedStripePayment(?FakeStripeCheckoutClient $stripe = null): array
{
    $stripe ??= new FakeStripeCheckoutClient();
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    (new StartCheckoutCommandHandler(
        $payments,
        stripeGateways($stripe),
        new PendingPayableOrder(),
    ))->handle(new StartCheckoutCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        provider: 'stripe',
        successUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=success',
        cancelUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=cancel',
    ));

    return [$payments, $dispatcher, $stripe];
}

it('confirms a paid stripe session when the customer returns from checkout', function () {
    [$payments, $dispatcher, $stripe] = startedStripePayment();
    $paidAt = new DateTimeImmutable('2026-08-20T12:05:00+00:00');
    $completed = [];
    $dispatcher->addListener(PaymentCompleted::class, static function (PaymentCompleted $event) use (&$completed): void {
        $completed[] = $event;
    });

    (new ConfirmPaymentCommandHandler(
        $payments,
        stripeGateways($stripe),
        new FixedClock($paidAt),
        $dispatcher,
        new PendingPayableOrder(),
    ))->handle(new ConfirmPaymentCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($payment?->status())->toEqual(PaymentStatus::completed())
        ->and($payment?->completedAt())->toBe($paidAt)
        ->and($completed)->toHaveCount(1)
        ->and($completed[0]->orderId)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');
});

it('leaves the payment pending when stripe has not captured the session yet', function () {
    $stripe = new FakeStripeCheckoutClient();
    $stripe->sessionPaid = false;
    [$payments, $dispatcher] = startedStripePayment($stripe);

    (new ConfirmPaymentCommandHandler(
        $payments,
        stripeGateways($stripe),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
        new PendingPayableOrder(),
    ))->handle(new ConfirmPaymentCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    expect($payment?->status())->toEqual(PaymentStatus::pending());
});

it('does not confirm a stripe session whose amount differs from the order', function () {
    $stripe = new FakeStripeCheckoutClient();
    $stripe->sessionAmountOverride = 100;
    [$payments, $dispatcher] = startedStripePayment($stripe);

    expect(fn () => (new ConfirmPaymentCommandHandler(
        $payments,
        stripeGateways($stripe),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
        new PendingPayableOrder(),
    ))->handle(new ConfirmPaymentCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    )))->toThrow(PaymentAmountMismatch::class);

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    expect($payment?->status())->toEqual(PaymentStatus::pending());
});
