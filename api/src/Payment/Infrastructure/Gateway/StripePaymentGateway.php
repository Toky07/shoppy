<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway;

use App\Payment\Application\PaymentGateway;
use App\Payment\Application\Port\PayerContact;
use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Application\Response\HostedCheckout;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Application\Response\PaymentCheckoutResult;
use App\Payment\Domain\Entity\Payment;
use App\Payment\Infrastructure\Gateway\Stripe\StripeCheckoutClient;

final class StripePaymentGateway implements PaymentGateway
{
    public const NAME = 'stripe';

    public function __construct(
        private StripeCheckoutClient $client,
        private string $currency,
        private PayerContact $payerContact,
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function charge(Payment $payment): PaymentChargeResult
    {
        return PaymentChargeResult::failure('Stripe payments must be started with checkout.');
    }

    public function startCheckout(Payment $payment, CheckoutContext $context): PaymentCheckoutResult
    {
        $session = $this->client->createSession(
            $payment->id()->value(),
            $payment->orderId()->value(),
            $payment->amount()->cents(),
            $this->currency,
            $context->successUrl,
            $context->cancelUrl,
            $payment->customerId()->value(),
            $this->payerContact->emailFor($payment->customerId()),
        );

        return PaymentCheckoutResult::hosted(self::NAME, $session->url, $session->id);
    }

    public function hostedCheckout(Payment $payment): ?HostedCheckout
    {
        $reference = $payment->providerReference();
        if ($reference === null || $reference === '') {
            return null;
        }

        $session = $this->client->retrieveSession($reference);
        if ($session === null) {
            return null;
        }

        return new HostedCheckout($session->paid, $session->amountCents);
    }

    public function expireCheckout(Payment $payment): void
    {
        $reference = $payment->providerReference();
        if ($reference === null || $reference === '') {
            return;
        }

        $this->client->expireSession($reference);
    }
}
