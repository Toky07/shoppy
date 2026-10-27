<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceQuantity extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice line quantity must be at least 1.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_quantity';
    }

    public function field(): string
    {
        return 'quantity';
    }
}
