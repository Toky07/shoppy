<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoicePdfPath extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice PDF path is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_pdf_path';
    }

    public function field(): string
    {
        return 'pdfPath';
    }
}
