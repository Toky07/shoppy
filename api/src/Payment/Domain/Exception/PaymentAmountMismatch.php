<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class PaymentAmountMismatch extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('Stripe amount does not match the order.');
    }

    public function errorCode(): string
    {
        return 'payment_amount_mismatch';
    }
}
