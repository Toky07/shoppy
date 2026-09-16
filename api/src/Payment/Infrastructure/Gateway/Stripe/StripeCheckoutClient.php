<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway\Stripe;

interface StripeCheckoutClient
{
    public function createSession(
        string $paymentId,
        string $orderId,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
    ): CreatedStripeSession;
}
