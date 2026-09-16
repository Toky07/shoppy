<?php

declare(strict_types=1);

use App\Payment\Application\Command\StartCheckoutCommand;
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

it('starts a stripe checkout through the registry and leaves the payment pending', function () {
    $payments = new InMemoryPaymentRepository();
    $dispatcher = new EventDispatcher();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        '11111111-1111-4111-8111-111111111111',
        3998,
    ));

    $completed = [];
    $dispatcher->addListener(PaymentCompleted::class, static function (PaymentCompleted $event) use (&$completed): void {
        $completed[] = $event;
    });

    $result = (new StartCheckoutCommandHandler(
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

    $payment = $payments->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($result->provider)->toBe('stripe')
        ->and($result->completedImmediately)->toBeFalse()
        ->and($result->redirectUrl)->toStartWith('https://checkout.test/')
        ->and($payment?->status())->toEqual(PaymentStatus::pending())
        ->and($payment?->provider())->toBe('stripe')
        ->and($payment?->providerReference())->toBe($result->providerReference)
        ->and($payment?->providerReference())->toStartWith('cs_test_')
        ->and($completed)->toHaveCount(0);
});
