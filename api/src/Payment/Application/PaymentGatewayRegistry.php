<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Domain\Exception\UnknownPaymentProvider;

final class PaymentGatewayRegistry
{
    /** @param iterable<PaymentGateway> $gateways */
    public function __construct(
        private iterable $gateways,
        private string $defaultProvider,
    ) {
    }

    public function default(): PaymentGateway
    {
        return $this->get($this->defaultProvider);
    }

    public function defaultName(): string
    {
        return $this->defaultProvider;
    }

    public function get(string $name): PaymentGateway
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->name() === $name) {
                return $gateway;
            }
        }

        throw new UnknownPaymentProvider($name);
    }
}
