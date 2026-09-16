<?php

declare(strict_types=1);

use App\Payment\Application\Command\HandleStripeWebhookCommand;
use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\CommandHandler\HandleStripeWebhookCommandHandler;
use App\Payment\Application\CommandHandler\StartCheckoutCommandHandler;
use App\Payment\Application\EventSubscriber\CreatePaymentOnOrderPlaced;
use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Payment\Infrastructure\Gateway\StripePaymentGateway;
use App\Payment\Infrastructure\Persistence\InMemoryPaymentRepository;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\FakeStripeCheckoutClient;
use App\Tests\Doubles\FixedClock;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('completes a pending stripe payment when checkout.session.completed is received', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $paidAt = new DateTimeImmutable('2026-08-20T12:05:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    $checkout = (new StartCheckoutCommandHandler(
        $payments,
        new PaymentGatewayRegistry([
            new LocalPaymentGateway(),
            new StripePaymentGateway(new FakeStripeCheckoutClient(), 'eur'),
        ], 'local'),
    ))->handle(new StartCheckoutCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        provider: 'stripe',
        successUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=success',
        cancelUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=cancel',
    ));

    $completed = [];
    $dispatcher->addListener(PaymentCompleted::class, static function (PaymentCompleted $event) use (&$completed): void {
        $completed[] = $event;
    });

    (new HandleStripeWebhookCommandHandler($payments, new FixedClock($paidAt), $dispatcher))
        ->handle(new HandleStripeWebhookCommand(
            type: 'checkout.session.completed',
            providerReference: (string) $checkout->providerReference,
        ));

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($payment?->status())->toEqual(PaymentStatus::completed())
        ->and($payment?->completedAt())->toBe($paidAt)
        ->and($completed)->toHaveCount(1)
        ->and($completed[0]->orderId)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($completed[0]->amountCents)->toBe(3998);
});
