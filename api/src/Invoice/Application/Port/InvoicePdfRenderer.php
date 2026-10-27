<?php

declare(strict_types=1);

namespace App\Invoice\Application\Port;

use App\Invoice\Domain\Entity\Invoice;

interface InvoicePdfRenderer
{
    public function render(Invoice $invoice, ?string $logoBinary): string;
}
