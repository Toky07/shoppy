<?php

declare(strict_types=1);

use App\Invoice\Domain\Exception\InvalidInvoiceLineDescription;
use App\Invoice\Domain\Exception\InvalidInvoiceQuantity;
use App\Invoice\Domain\Exception\InvalidInvoiceUnitPrice;
use App\Invoice\Domain\ValueObject\InvoiceLine;

it('snapshots a billed line with its total', function () {
    $line = InvoiceLine::of('Nuvora Tee', 2, 1999);

    expect($line->description())->toBe('Nuvora Tee')
        ->and($line->quantity())->toBe(2)
        ->and($line->unitPriceCents())->toBe(1999)
        ->and($line->lineTotalCents())->toBe(3998);
});

it('trims the line description', function () {
    expect(InvoiceLine::of('  Livraison Standard  ', 1, 490)->description())->toBe('Livraison Standard');
});

it('allows a free shipping line', function () {
    expect(InvoiceLine::of('Livraison Standard', 1, 0)->lineTotalCents())->toBe(0);
});

it('rejects an empty description', function () {
    InvoiceLine::of('   ', 1, 1999);
})->throws(InvalidInvoiceLineDescription::class);

it('rejects a quantity below one', function () {
    InvoiceLine::of('Nuvora Tee', 0, 1999);
})->throws(InvalidInvoiceQuantity::class);

it('rejects a negative unit price', function () {
    InvoiceLine::of('Nuvora Tee', 1, -1);
})->throws(InvalidInvoiceUnitPrice::class);
