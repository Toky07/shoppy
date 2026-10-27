<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class InvoiceAmountMismatch extends \RuntimeException implements DomainException
{
    public function __construct()
    {
        parent::__construct('Invoice lines must add up to the invoice amount.');
    }

    public function errorCode(): string
    {
        return 'invoice_amount_mismatch';
    }
}
