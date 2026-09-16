<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway;

use App\Payment\Application\Response\StripeWebhookEvent;
use App\Payment\Application\StripeWebhookParser;
use App\Payment\Domain\Exception\InvalidPaymentWebhook;

final class UnconfiguredStripeWebhookParser implements StripeWebhookParser
{
    public function parse(string $payload, string $signature): StripeWebhookEvent
    {
        throw new InvalidPaymentWebhook();
    }
}
