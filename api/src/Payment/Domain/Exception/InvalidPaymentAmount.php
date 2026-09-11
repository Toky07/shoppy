<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPaymentAmount extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Payment amount cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_payment_amount';
    }

    public function field(): string
    {
        return 'amountCents';
    }
}
