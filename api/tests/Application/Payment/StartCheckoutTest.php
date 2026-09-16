<?php

declare(strict_types=1);

use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Payment\Domain\ValueObject\PaymentStatus;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Payment\Infrastructure\Gateway\StripePaymentGateway;
use App\Tests\Doubles\FakeStripeCheckoutClient;

function pendingCheckoutPayment(): Payment
{
    return Payment::createPending(
        PaymentId::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        MoneyAmount::fromCents(3998),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
}

function checkoutUrls(): CheckoutContext
{
    return new CheckoutContext(
        successUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=success',
        cancelUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=cancel',
    );
}

it('starts a local checkout as an immediate charge', function () {
    $payment = pendingCheckoutPayment();
    $result = (new LocalPaymentGateway())->startCheckout($payment, checkoutUrls());

    expect($result->provider)->toBe('local')
        ->and($result->completedImmediately)->toBeTrue()
        ->and($result->redirectUrl)->toBeNull()
        ->and($payment->status())->toEqual(PaymentStatus::pending());
});

it('starts a stripe checkout with a redirect url and leaves the payment pending', function () {
    $payment = pendingCheckoutPayment();
    $result = (new StripePaymentGateway(new FakeStripeCheckoutClient(), 'eur'))
        ->startCheckout($payment, checkoutUrls());

    expect($result->provider)->toBe('stripe')
        ->and($result->completedImmediately)->toBeFalse()
        ->and($result->redirectUrl)->toBe('https://checkout.test/cs_test_dddddddd-dddd-4ddd-8ddd-dddddddddddd')
        ->and($result->providerReference)->toBe('cs_test_dddddddd-dddd-4ddd-8ddd-dddddddddddd')
        ->and($payment->status())->toEqual(PaymentStatus::pending());
});
