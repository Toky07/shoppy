<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoicePdfPath;

final readonly class InvoicePdf
{
    public const MIME_TYPE = 'application/pdf';

    private const PATH_PATTERN = '/^invoices\/pdf\/FAC-\d{4}-\d{4,}\.pdf$/';

    private function __construct(private string $relativePath)
    {
    }

    public static function of(string $relativePath): self
    {
        $normalized = str_replace('\\', '/', trim($relativePath));

        if (preg_match(self::PATH_PATTERN, $normalized) !== 1 || str_contains($normalized, '..')) {
            throw new InvalidInvoicePdfPath();
        }

        return new self($normalized);
    }

    public static function pathFor(InvoiceNumber $number): self
    {
        return self::of('invoices/pdf/'.$number->value().'.pdf');
    }

    public function relativePath(): string
    {
        return $this->relativePath;
    }

    public function filename(): string
    {
        return basename($this->relativePath);
    }

    public function mimeType(): string
    {
        return self::MIME_TYPE;
    }
}
