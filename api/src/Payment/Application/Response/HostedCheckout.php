<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

final readonly class HostedCheckout
{
    public function __construct(
        public bool $paid,
        public ?int $amountCents,
    ) {
    }
}
