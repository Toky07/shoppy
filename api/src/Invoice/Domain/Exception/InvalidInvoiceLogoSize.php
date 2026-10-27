<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceLogoSize extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice logo size must be between 1 byte and 2 MB.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_logo_size';
    }

    public function field(): string
    {
        return 'file';
    }
}
