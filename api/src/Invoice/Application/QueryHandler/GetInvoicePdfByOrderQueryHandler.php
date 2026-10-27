<?php

declare(strict_types=1);

namespace App\Invoice\Application\QueryHandler;

use App\Invoice\Application\Port\InvoicePdfStorage;
use App\Invoice\Application\Query\GetInvoicePdfByOrderQuery;
use App\Invoice\Application\Response\InvoicePdfFile;
use App\Invoice\Domain\Exception\InvoiceNotFound;
use App\Invoice\Domain\Exception\InvoicePdfNotFound;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\OrderReference;

final readonly class GetInvoicePdfByOrderQueryHandler
{
    public function __construct(
        private InvoiceRepository $invoices,
        private InvoicePdfStorage $pdfs,
    ) {
    }

    public function handle(GetInvoicePdfByOrderQuery $query): InvoicePdfFile
    {
        $orderId = OrderReference::fromString($query->orderId);
        $invoice = $this->invoices->findByOrderId($orderId);

        if ($invoice === null) {
            throw new InvoiceNotFound($orderId);
        }

        $pdf = $invoice->pdf();
        $content = $pdf === null ? null : $this->pdfs->read($pdf->relativePath());

        if ($pdf === null || $content === null) {
            throw new InvoicePdfNotFound($invoice->number());
        }

        return new InvoicePdfFile($pdf->filename(), $pdf->mimeType(), $content);
    }
}
