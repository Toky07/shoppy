<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPaymentId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Payment id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_payment_id';
    }

    public function field(): string
    {
        return 'id';
    }
}
