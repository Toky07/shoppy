<?php

declare(strict_types=1);

use App\Invoice\Domain\Exception\InvalidInvoicePdfPath;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\InvoicePdf;

it('builds a downloadable pdf path from the invoice number', function () {
    $pdf = InvoicePdf::pathFor(InvoiceNumber::fromString('FAC-2026-0001'));

    expect($pdf->relativePath())->toBe('invoices/pdf/FAC-2026-0001.pdf')
        ->and($pdf->filename())->toBe('FAC-2026-0001.pdf')
        ->and($pdf->mimeType())->toBe('application/pdf');
});

it('rejects a path outside the invoice pdf directory', function (string $path) {
    InvoicePdf::of($path);
})->with(['FAC-2026-0001.pdf', 'invoices/pdf/../FAC-2026-0001.pdf', 'invoices/pdf/invoice.txt'])->throws(InvalidInvoicePdfPath::class);
