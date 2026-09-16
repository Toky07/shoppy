<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPaymentWebhook extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invalid payment webhook signature.');
    }

    public function errorCode(): string
    {
        return 'invalid_payment_webhook';
    }

    public function field(): string
    {
        return 'signature';
    }
}
