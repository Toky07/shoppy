<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Domain\Exception\LocalPaymentDisabled;

final readonly class PaymentProviderPolicy
{
    public const LOCAL = 'local';

    public function __construct(
        private string $provider,
        private string $environment,
    ) {
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function assertLocalCompletionAllowed(): void
    {
        if ($this->environment === 'prod' || $this->provider !== self::LOCAL) {
            throw new LocalPaymentDisabled();
        }
    }

    public function assertCheckoutAllowed(): void
    {
        if ($this->environment === 'prod' && $this->provider === self::LOCAL) {
            throw new LocalPaymentDisabled();
        }
    }
}
