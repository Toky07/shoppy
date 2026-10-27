<?php

declare(strict_types=1);

use App\Invoice\Application\EventSubscriber\GenerateInvoicePdfOnInvoiceIssued;
use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Shared\Application\Event\InvoiceIssued;
use App\Tests\Doubles\FakeInvoiceLogoStorage;
use App\Tests\Doubles\FakeInvoicePdfRenderer;
use App\Tests\Doubles\FakeInvoicePdfStorage;

function issuedInvoice(?InvoiceLogo $logo = null): Invoice
{
    return Invoice::issue(
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
        $logo,
    );
}

function invoiceIssuedEvent(): InvoiceIssued
{
    return new InvoiceIssued(
        invoiceId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        number: 'FAC-2026-0001',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        amountCents: 4488,
    );
}

it('generates and stores a pdf when an invoice is issued', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(issuedInvoice());
    $renderer = new FakeInvoicePdfRenderer();
    $pdfs = new FakeInvoicePdfStorage();

    (new GenerateInvoicePdfOnInvoiceIssued($invoices, $renderer, $pdfs, new FakeInvoiceLogoStorage()))
        ->onInvoiceIssued(invoiceIssuedEvent());

    $invoice = $invoices->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($invoice?->pdf()?->relativePath())->toBe('invoices/pdf/FAC-2026-0001.pdf')
        ->and($pdfs->files['invoices/pdf/FAC-2026-0001.pdf'])->toBe("%PDF-1.4\nFAC-2026-0001\n")
        ->and($renderer->lastInvoice?->number()->value())->toBe('FAC-2026-0001')
        ->and($renderer->lastLogoBinary)->toBeNull();
});

it('passes the snapshotted logo to the pdf renderer', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(issuedInvoice(InvoiceLogo::of('invoices/logos/shop.png', 'image/png')));
    $logos = new FakeInvoiceLogoStorage();
    $logos->files['invoices/logos/shop.png'] = 'png-bytes';
    $renderer = new FakeInvoicePdfRenderer();

    (new GenerateInvoicePdfOnInvoiceIssued($invoices, $renderer, new FakeInvoicePdfStorage(), $logos))
        ->onInvoiceIssued(invoiceIssuedEvent());

    expect($renderer->lastLogoBinary)->toBe('png-bytes');
});

it('does not generate a second pdf for the same invoice', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(issuedInvoice());
    $renderer = new FakeInvoicePdfRenderer();
    $pdfs = new FakeInvoicePdfStorage();
    $subscriber = new GenerateInvoicePdfOnInvoiceIssued($invoices, $renderer, $pdfs, new FakeInvoiceLogoStorage());

    $subscriber->onInvoiceIssued(invoiceIssuedEvent());
    $subscriber->onInvoiceIssued(invoiceIssuedEvent());

    expect($pdfs->files)->toHaveCount(1);
});
