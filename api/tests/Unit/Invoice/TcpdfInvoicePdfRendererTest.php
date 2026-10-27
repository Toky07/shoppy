<?php

declare(strict_types=1);

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Invoice\Infrastructure\Pdf\TcpdfInvoicePdfRenderer;

it('renders a detailed invoice pdf with lines and totals', function () {
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
    $logo = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);

    $pdf = (new TcpdfInvoicePdfRenderer())->render($invoice, $logo);

    expect($pdf)->toStartWith('%PDF-')
        ->and($pdf)->toContain('FAC-2026-0001')
        ->and($pdf)->toContain('Nuvora Tee')
        ->and($pdf)->toContain('Livraison Standard')
        ->and($pdf)->toContain('44,88 EUR');
});
