<?php

declare(strict_types=1);

namespace App\Invoice\Application;

use App\Invoice\Domain\Exception\InvalidInvoiceLogoMimeType;
use finfo;

final class DetectedInvoiceLogoType
{
    /** @var array<string, string> */
    private const EXTENSIONS = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
    ];

    public function __construct(private string $mimeType)
    {
    }

    public static function fromBinary(string $contents): self
    {
        if ($contents === '') {
            throw new InvalidInvoiceLogoMimeType();
        }

        $detected = strtolower((string) (new finfo(FILEINFO_MIME_TYPE))->buffer($contents));

        if (!isset(self::EXTENSIONS[$detected])) {
            throw new InvalidInvoiceLogoMimeType();
        }

        return new self($detected);
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function extension(): string
    {
        return self::EXTENSIONS[$this->mimeType];
    }
}
