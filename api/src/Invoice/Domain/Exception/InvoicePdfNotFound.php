<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Shared\Domain\Exception\NotFoundException;

final class InvoicePdfNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(InvoiceNumber $number)
    {
        parent::__construct(sprintf('PDF for invoice "%s" was not found.', $number->value()));
    }

    public function errorCode(): string
    {
        return 'invoice_pdf_not_found';
    }
}
