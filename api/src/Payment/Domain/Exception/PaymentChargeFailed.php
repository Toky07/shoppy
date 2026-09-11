<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class PaymentChargeFailed extends \RuntimeException implements ConflictException
{
    public function __construct(string $reason = 'Payment charge failed.')
    {
        parent::__construct($reason);
    }

    public function errorCode(): string
    {
        return 'payment_charge_failed';
    }
}
