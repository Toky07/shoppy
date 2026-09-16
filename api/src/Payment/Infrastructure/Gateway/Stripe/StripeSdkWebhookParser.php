<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway\Stripe;

use App\Payment\Application\Response\StripeWebhookEvent;
use App\Payment\Application\StripeWebhookParser;
use App\Payment\Domain\Exception\InvalidPaymentWebhook;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

final class StripeSdkWebhookParser implements StripeWebhookParser
{
    public function __construct(private string $webhookSecret)
    {
    }

    public function parse(string $payload, string $signature): StripeWebhookEvent
    {
        if ($this->webhookSecret === '' || $signature === '') {
            throw new InvalidPaymentWebhook();
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            throw new InvalidPaymentWebhook();
        }

        $sessionId = $event->data->object->id ?? null;
        if (!is_string($sessionId) || $sessionId === '') {
            throw new InvalidPaymentWebhook();
        }

        return new StripeWebhookEvent((string) $event->type, $sessionId);
    }
}
