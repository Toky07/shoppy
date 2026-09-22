<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Application\Response\StripeWebhookEvent;
use App\Payment\Application\StripeWebhookParser;
use App\Payment\Domain\Exception\InvalidPaymentWebhook;

final class FakeStripeWebhookParser implements StripeWebhookParser
{
    public const VALID_SIGNATURE = 'test';

    public function parse(string $payload, string $signature): StripeWebhookEvent
    {
        if ($signature !== self::VALID_SIGNATURE) {
            throw new InvalidPaymentWebhook();
        }

        $decoded = json_decode($payload, true);

        if (
            !is_array($decoded)
            || !isset($decoded['type'], $decoded['data']['object']['id'])
            || !is_string($decoded['type'])
            || !is_string($decoded['data']['object']['id'])
        ) {
            throw new InvalidPaymentWebhook();
        }

        $amountCents = $decoded['data']['object']['amount_total'] ?? null;
        if ($decoded['type'] === 'checkout.session.completed' && !is_int($amountCents)) {
            throw new InvalidPaymentWebhook();
        }

        return new StripeWebhookEvent(
            $decoded['type'],
            $decoded['data']['object']['id'],
            is_int($amountCents) ? $amountCents : null,
        );
    }
}
