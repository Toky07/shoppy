<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPaymentStatus extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Payment status must be pending, completed, or cancelled.');
    }

    public function errorCode(): string
    {
        return 'invalid_payment_status';
    }

    public function field(): string
    {
        return 'status';
    }
}
