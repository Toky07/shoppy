<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway\Stripe;

use App\Payment\Domain\Exception\PaymentChargeFailed;
use App\Payment\Domain\Exception\PaymentProviderNotConfigured;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

final class StripeSdkCheckoutClient implements StripeCheckoutClient
{
    public function __construct(private string $secretKey)
    {
    }

    public function createSession(
        string $paymentId,
        string $orderId,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
    ): CreatedStripeSession {
        if ($this->secretKey === '') {
            throw new PaymentProviderNotConfigured();
        }

        try {
            $session = (new StripeClient($this->secretKey))->checkout->sessions->create([
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => $paymentId,
                'metadata' => [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                ],
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amountCents,
                        'product_data' => [
                            'name' => 'Commande',
                        ],
                    ],
                ]],
            ]);
        } catch (ApiErrorException $exception) {
            throw new PaymentChargeFailed($exception->getMessage());
        }

        if (!is_string($session->id) || $session->id === '' || !is_string($session->url) || $session->url === '') {
            throw new PaymentChargeFailed('Stripe checkout session is missing a redirect URL.');
        }

        return new CreatedStripeSession($session->id, $session->url);
    }

    public function expireSession(string $sessionId): void
    {
        if ($this->secretKey === '' || $sessionId === '') {
            return;
        }

        try {
            (new StripeClient($this->secretKey))->checkout->sessions->expire($sessionId);
        } catch (ApiErrorException) {
            // The session is already expired or completed. Cancellation still proceeds.
        }
    }
}
