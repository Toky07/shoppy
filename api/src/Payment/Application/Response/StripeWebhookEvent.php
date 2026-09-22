<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

final readonly class StripeWebhookEvent
{
    public function __construct(
        public string $type,
        public string $sessionId,
        public ?int $amountCents = null,
    ) {
    }
}
