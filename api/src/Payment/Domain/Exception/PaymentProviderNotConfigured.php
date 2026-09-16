<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class PaymentProviderNotConfigured extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('The payment provider is not configured.');
    }

    public function errorCode(): string
    {
        return 'payment_provider_not_configured';
    }
}
