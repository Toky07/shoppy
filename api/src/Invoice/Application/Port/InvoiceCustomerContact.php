<?php

declare(strict_types=1);

namespace App\Invoice\Application\Port;

use App\Invoice\Domain\ValueObject\CustomerReference;

interface InvoiceCustomerContact
{
    public function emailFor(CustomerReference $customerId): ?string;
}
