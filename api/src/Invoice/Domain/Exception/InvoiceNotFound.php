<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Invoice\Domain\ValueObject\OrderReference;
use App\Shared\Domain\Exception\NotFoundException;

final class InvoiceNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(OrderReference $orderId)
    {
        parent::__construct(sprintf('Invoice for order "%s" was not found.', $orderId->value()));
    }

    public function errorCode(): string
    {
        return 'invoice_not_found';
    }
}
