<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceNumber extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice number must look like FAC-2026-0001.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_number';
    }

    public function field(): string
    {
        return 'number';
    }
}
