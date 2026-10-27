<?php

declare(strict_types=1);

use App\Invoice\Domain\Exception\InvalidInvoiceLogoMimeType;
use App\Invoice\Domain\Exception\InvalidInvoiceLogoPath;
use App\Invoice\Domain\ValueObject\InvoiceLogo;

it('exposes a public url for a stored shop logo', function () {
    $logo = InvoiceLogo::of('invoices/logos/shop.png', 'image/png');

    expect($logo->relativePath())->toBe('invoices/logos/shop.png')
        ->and($logo->mimeType())->toBe('image/png')
        ->and($logo->publicUrl())->toBe('/uploads/invoices/logos/shop.png');
});

it('rejects an unsupported logo mime type', function () {
    InvoiceLogo::of('invoices/logos/shop.gif', 'image/gif');
})->throws(InvalidInvoiceLogoMimeType::class);

it('rejects a path outside the invoice logo directory', function (string $path) {
    InvoiceLogo::of($path, 'image/png');
})->with(['shop.png', '/invoices/logos/shop.png', 'invoices/logos/../shop.png'])->throws(InvalidInvoiceLogoPath::class);
