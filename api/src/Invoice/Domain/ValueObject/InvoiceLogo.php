<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoiceLogoMimeType;
use App\Invoice\Domain\Exception\InvalidInvoiceLogoPath;

final readonly class InvoiceLogo
{
    public const PUBLIC_PREFIX = '/uploads/';

    /** @var array<string, string> */
    private const EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
    ];

    private const PATH_PATTERN = '/^invoices\/logos\/[a-zA-Z0-9._-]+$/';

    private function __construct(
        private string $relativePath,
        private string $mimeType,
    ) {
    }

    public static function of(string $relativePath, string $mimeType): self
    {
        $normalizedMime = strtolower(trim($mimeType));

        if (!isset(self::EXTENSIONS[$normalizedMime])) {
            throw new InvalidInvoiceLogoMimeType();
        }

        $normalizedPath = str_replace('\\', '/', trim($relativePath));

        if (preg_match(self::PATH_PATTERN, $normalizedPath) !== 1 || str_contains($normalizedPath, '..')) {
            throw new InvalidInvoiceLogoPath();
        }

        if (!str_ends_with($normalizedPath, '.'.self::EXTENSIONS[$normalizedMime])) {
            throw new InvalidInvoiceLogoPath();
        }

        return new self($normalizedPath, $normalizedMime);
    }

    public function relativePath(): string
    {
        return $this->relativePath;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function extension(): string
    {
        return self::EXTENSIONS[$this->mimeType];
    }

    public function publicUrl(): string
    {
        return self::PUBLIC_PREFIX.$this->relativePath;
    }
}
