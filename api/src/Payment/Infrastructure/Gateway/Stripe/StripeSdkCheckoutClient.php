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
        string $userId,
        ?string $email = null,
    ): CreatedStripeSession {
        $this->assertConfigured();

        try {
            $session = (new StripeClient($this->secretKey))->checkout->sessions->create(
                $this->sessionParameters(
                    $paymentId,
                    $orderId,
                    $amountCents,
                    $currency,
                    $successUrl,
                    $cancelUrl,
                    $userId,
                    $email,
                ),
            );
        } catch (ApiErrorException $exception) {
            throw new PaymentChargeFailed($exception->getMessage());
        }

        if (!is_string($session->id) || $session->id === '' || !is_string($session->url) || $session->url === '') {
            throw new PaymentChargeFailed('Stripe checkout session is missing a redirect URL.');
        }

        return new CreatedStripeSession($session->id, $session->url);
    }

    public function retrieveSession(string $sessionId): ?RetrievedStripeSession
    {
        $this->assertConfigured();

        if ($sessionId === '') {
            return null;
        }

        try {
            $session = (new StripeClient($this->secretKey))->checkout->sessions->retrieve($sessionId);
        } catch (ApiErrorException) {
            return null;
        }

        $amount = $session->amount_total ?? null;

        return new RetrievedStripeSession(
            is_string($session->id) ? $session->id : $sessionId,
            ($session->payment_status ?? '') === 'paid',
            is_int($amount) ? $amount : null,
        );
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

    /**
     * @return array<string, mixed>
     */
    private function sessionParameters(
        string $paymentId,
        string $orderId,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
        string $userId,
        ?string $email,
    ): array {
        $metadata = [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'userId' => $userId,
        ];

        if ($email !== null && $email !== '') {
            $metadata['email'] = $email;
        }

        $parameters = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $paymentId,
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
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
        ];

        if ($email !== null && $email !== '') {
            $parameters['customer_email'] = $email;
        }

        return $parameters;
    }

    private function assertConfigured(): void
    {
        if ($this->secretKey === '' || !str_starts_with($this->secretKey, 'sk_')) {
            throw new PaymentProviderNotConfigured();
        }
    }
}
