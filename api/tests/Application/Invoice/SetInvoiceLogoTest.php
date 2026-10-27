<?php

declare(strict_types=1);

use App\Invoice\Application\Command\SetInvoiceLogoCommand;
use App\Invoice\Application\CommandHandler\SetInvoiceLogoCommandHandler;
use App\Invoice\Domain\Exception\InvalidInvoiceLogoMimeType;
use App\Invoice\Domain\Exception\InvalidInvoiceLogoSize;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceBrandingRepository;
use App\Tests\Doubles\FakeInvoiceLogoStorage;

function invoicePng(): string
{
    return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

it('stores the shop invoice logo', function () {
    $branding = new InMemoryInvoiceBrandingRepository();
    $storage = new FakeInvoiceLogoStorage();
    $logo = (new SetInvoiceLogoCommandHandler($branding, $storage))->handle(new SetInvoiceLogoCommand(invoicePng()));

    expect($logo->relativePath())->toBe('invoices/logos/shop.png')
        ->and($logo->mimeType())->toBe('image/png')
        ->and($logo->publicUrl())->toBe('/uploads/invoices/logos/shop.png')
        ->and($branding->get()->logo()?->relativePath())->toBe('invoices/logos/shop.png')
        ->and($storage->files['invoices/logos/shop.png'])->toBe(invoicePng());
});

it('rejects an empty logo', function () {
    (new SetInvoiceLogoCommandHandler(
        new InMemoryInvoiceBrandingRepository(),
        new FakeInvoiceLogoStorage(),
    ))->handle(new SetInvoiceLogoCommand(''));
})->throws(InvalidInvoiceLogoSize::class);

it('rejects a logo that is not a png or jpeg', function () {
    (new SetInvoiceLogoCommandHandler(
        new InMemoryInvoiceBrandingRepository(),
        new FakeInvoiceLogoStorage(),
    ))->handle(new SetInvoiceLogoCommand('not-an-image'));
})->throws(InvalidInvoiceLogoMimeType::class);
