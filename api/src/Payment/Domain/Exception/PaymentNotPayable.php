<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class PaymentNotPayable extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('Payment cannot be completed in its current state.');
    }

    public function errorCode(): string
    {
        return 'payment_not_payable';
    }
}
