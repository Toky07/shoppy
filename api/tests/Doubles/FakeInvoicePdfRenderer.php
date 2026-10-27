<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Invoice\Application\Port\InvoicePdfRenderer;
use App\Invoice\Domain\Entity\Invoice;

final class FakeInvoicePdfRenderer implements InvoicePdfRenderer
{
    public ?Invoice $lastInvoice = null;

    public ?string $lastLogoBinary = null;

    public function render(Invoice $invoice, ?string $logoBinary): string
    {
        $this->lastInvoice = $invoice;
        $this->lastLogoBinary = $logoBinary;

        return "%PDF-1.4\n".$invoice->number()->value()."\n";
    }
}
