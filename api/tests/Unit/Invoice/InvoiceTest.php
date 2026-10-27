<?php

declare(strict_types=1);

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Exception\InvoiceAmountMismatch;
use App\Invoice\Domain\Exception\InvoicePdfAlreadyAttached;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\InvoicePdf;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;

function issueInvoice(int $amountCents, InvoiceLine ...$lines): Invoice
{
    return Invoice::issue(
        InvoiceId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff'),
        InvoiceNumber::fromString('FAC-2026-0001'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        MoneyAmount::fromCents($amountCents),
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        $lines,
    );
}

it('keeps billed lines on the issued invoice', function () {
    $invoice = issueInvoice(
        4488,
        InvoiceLine::of('Nuvora Tee', 2, 1999),
        InvoiceLine::of('Livraison Standard', 1, 490),
    );

    expect($invoice->lines())->toHaveCount(2)
        ->and($invoice->lines()[0]->description())->toBe('Nuvora Tee')
        ->and($invoice->lines()[0]->lineTotalCents())->toBe(3998)
        ->and($invoice->lines()[1]->description())->toBe('Livraison Standard')
        ->and($invoice->amount()->cents())->toBe(4488);
});

it('rejects lines that do not add up to the invoice amount', function () {
    issueInvoice(4488, InvoiceLine::of('Nuvora Tee', 2, 1999));
})->throws(InvoiceAmountMismatch::class);

it('keeps the shop logo that was current when the invoice was issued', function () {
    $logo = InvoiceLogo::of('invoices/logos/shop.png', 'image/png');
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
        $logo,
    );

    expect($invoice->logo())->toBe($logo)
        ->and($invoice->logo()?->publicUrl())->toBe('/uploads/invoices/logos/shop.png');
});

it('attaches a generated pdf once', function () {
    $invoice = issueInvoice(
        4488,
        InvoiceLine::of('Nuvora Tee', 2, 1999),
        InvoiceLine::of('Livraison Standard', 1, 490),
    );
    $pdf = InvoicePdf::pathFor($invoice->number());

    $invoice->attachPdf($pdf);

    expect($invoice->pdf())->toBe($pdf)
        ->and($invoice->pdf()?->filename())->toBe('FAC-2026-0001.pdf');
});

it('rejects attaching a second pdf', function () {
    $invoice = issueInvoice(
        4488,
        InvoiceLine::of('Nuvora Tee', 2, 1999),
        InvoiceLine::of('Livraison Standard', 1, 490),
    );
    $invoice->attachPdf(InvoicePdf::pathFor($invoice->number()));
    $invoice->attachPdf(InvoicePdf::pathFor($invoice->number()));
})->throws(InvoicePdfAlreadyAttached::class);
