<?php

declare(strict_types=1);

namespace App\Payment\Application\Command;

final readonly class HandleStripeWebhookCommand
{
    public function __construct(
        public string $type,
        public string $providerReference,
    ) {
    }
}
