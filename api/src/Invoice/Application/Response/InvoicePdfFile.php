<?php

declare(strict_types=1);

namespace App\Invoice\Application\Response;

final readonly class InvoicePdfFile
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $content,
    ) {
    }
}
