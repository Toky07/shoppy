<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceLogoPath extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice logo path is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_logo_path';
    }

    public function field(): string
    {
        return 'logoPath';
    }
}
