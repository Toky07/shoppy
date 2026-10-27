<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidInvoiceLineDescription extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Invoice line description cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_line_description';
    }

    public function field(): string
    {
        return 'description';
    }
}
