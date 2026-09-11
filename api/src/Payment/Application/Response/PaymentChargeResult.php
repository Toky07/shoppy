<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

final readonly class PaymentChargeResult
{
    private function __construct(
        public bool $successful,
        public ?string $failureReason,
    ) {
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }
}
