<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceUnitPrice extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice unit price cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_unit_price';
    }

    public function field(): string
    {
        return 'unitPriceCents';
    }
}
