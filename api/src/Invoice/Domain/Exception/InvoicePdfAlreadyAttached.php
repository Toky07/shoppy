<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class InvoicePdfAlreadyAttached extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('Invoice PDF has already been generated.');
    }

    public function errorCode(): string
    {
        return 'invoice_pdf_already_attached';
    }
}
