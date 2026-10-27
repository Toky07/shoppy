<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceLogoMimeType extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice logo must be a PNG or JPEG image.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_logo_mime_type';
    }

    public function field(): string
    {
        return 'file';
    }
}
