<?php

declare(strict_types=1);

use App\Invoice\Domain\Exception\InvalidInvoiceNumber;
use App\Invoice\Domain\ValueObject\InvoiceNumber;

it('formats a sequential french invoice number', function () {
    $number = InvoiceNumber::of(2026, 1);

    expect($number->value())->toBe('FAC-2026-0001')
        ->and($number->year())->toBe(2026)
        ->and($number->sequence())->toBe(1);
});

it('keeps extra digits when the yearly sequence exceeds 9999', function () {
    expect(InvoiceNumber::of(2026, 10000)->value())->toBe('FAC-2026-10000');
});

it('reconstitutes a number from its formatted value', function () {
    $number = InvoiceNumber::fromString('FAC-2026-0042');

    expect($number->value())->toBe('FAC-2026-0042')
        ->and($number->year())->toBe(2026)
        ->and($number->sequence())->toBe(42);
});

it('rejects an invalid invoice number', function (string $value) {
    InvoiceNumber::fromString($value);
})->with(['', 'INV-2026-0001', 'FAC-26-0001', 'FAC-2026-1'])->throws(InvalidInvoiceNumber::class);

it('rejects an invalid year or sequence', function (int $year, int $sequence) {
    InvoiceNumber::of($year, $sequence);
})->with([[1999, 1], [2101, 1], [2026, 0]])->throws(InvalidInvoiceNumber::class);
