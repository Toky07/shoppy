<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class PaymentAlreadyCompleted extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('Payment is already completed.');
    }

    public function errorCode(): string
    {
        return 'payment_already_completed';
    }
}
