<?php

declare(strict_types=1);

namespace App\Invoice\Application\EventSubscriber;

use App\Invoice\Application\Port\InvoiceLogoStorage;
use App\Invoice\Application\Port\InvoicePdfRenderer;
use App\Invoice\Application\Port\InvoicePdfStorage;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\InvoicePdf;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\InvoiceIssued;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class GenerateInvoicePdfOnInvoiceIssued implements EventSubscriberInterface
{
    public function __construct(
        private InvoiceRepository $invoices,
        private InvoicePdfRenderer $renderer,
        private InvoicePdfStorage $pdfs,
        private InvoiceLogoStorage $logos,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [InvoiceIssued::class => ['onInvoiceIssued', 16]];
    }

    public function onInvoiceIssued(InvoiceIssued $event): void
    {
        $invoice = $this->invoices->findByOrderId(OrderReference::fromString($event->orderId));

        if ($invoice === null || $invoice->pdf() !== null) {
            return;
        }

        $logoBinary = null;
        if ($invoice->logo() !== null) {
            $logoBinary = $this->logos->read($invoice->logo()->relativePath());
        }

        $pdf = InvoicePdf::pathFor($invoice->number());
        $this->pdfs->store($pdf->relativePath(), $this->renderer->render($invoice, $logoBinary));
        $invoice->attachPdf($pdf);
        $this->invoices->save($invoice);
    }
}
