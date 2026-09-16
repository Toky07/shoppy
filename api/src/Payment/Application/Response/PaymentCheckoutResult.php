<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

final readonly class PaymentCheckoutResult
{
    private function __construct(
        public string $provider,
        public bool $completedImmediately,
        public ?string $redirectUrl,
        public ?string $providerReference,
    ) {
    }

    public static function immediate(string $provider): self
    {
        return new self($provider, true, null, null);
    }

    public static function hosted(string $provider, string $redirectUrl, string $providerReference): self
    {
        return new self($provider, false, $redirectUrl, $providerReference);
    }
}
