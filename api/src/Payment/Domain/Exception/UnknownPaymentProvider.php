<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class UnknownPaymentProvider extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(string $provider = '')
    {
        parent::__construct($provider === '' ? 'Unknown payment provider.' : sprintf('Unknown payment provider "%s".', $provider));
    }

    public function errorCode(): string
    {
        return 'unknown_payment_provider';
    }

    public function field(): string
    {
        return 'provider';
    }
}
