<?php

declare(strict_types=1);

use App\Invoice\Application\Query\GetInvoicePdfByOrderQuery;
use App\Invoice\Application\QueryHandler\GetInvoicePdfByOrderQueryHandler;
use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Exception\InvoiceNotFound;
use App\Invoice\Domain\Exception\InvoicePdfNotFound;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\InvoicePdf;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Tests\Doubles\FakeInvoicePdfStorage;

function pdfInvoice(bool $withPdf = false): Invoice
{
    $invoice = Invoice::issue(
        InvoiceId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff'),
        InvoiceNumber::fromString('FAC-2026-0001'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        MoneyAmount::fromCents(4488),
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        [
            InvoiceLine::of('Nuvora Tee', 2, 1999),
            InvoiceLine::of('Livraison Standard', 1, 490),
        ],
    );

    if ($withPdf) {
        $invoice->attachPdf(InvoicePdf::pathFor($invoice->number()));
    }

    return $invoice;
}

it('returns the stored invoice pdf', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(pdfInvoice(withPdf: true));
    $pdfs = new FakeInvoicePdfStorage();
    $pdfs->store('invoices/pdf/FAC-2026-0001.pdf', "%PDF-1.4\nFAC-2026-0001\n");

    $file = (new GetInvoicePdfByOrderQueryHandler($invoices, $pdfs))->handle(
        new GetInvoicePdfByOrderQuery('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
    );

    expect($file->filename)->toBe('FAC-2026-0001.pdf')
        ->and($file->mimeType)->toBe('application/pdf')
        ->and($file->content)->toBe("%PDF-1.4\nFAC-2026-0001\n");
});

it('rejects an unknown order invoice', function () {
    (new GetInvoicePdfByOrderQueryHandler(new InMemoryInvoiceRepository(), new FakeInvoicePdfStorage()))
        ->handle(new GetInvoicePdfByOrderQuery('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
})->throws(InvoiceNotFound::class);

it('rejects an invoice that has no pdf yet', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(pdfInvoice());

    (new GetInvoicePdfByOrderQueryHandler($invoices, new FakeInvoicePdfStorage()))
        ->handle(new GetInvoicePdfByOrderQuery('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
})->throws(InvoicePdfNotFound::class);
