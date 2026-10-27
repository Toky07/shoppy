<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceAmount extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice amount cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_amount';
    }

    public function field(): string
    {
        return 'amountCents';
    }
}
