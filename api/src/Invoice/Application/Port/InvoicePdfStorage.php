<?php

declare(strict_types=1);

namespace App\Invoice\Application\Port;

interface InvoicePdfStorage
{
    public function store(string $relativePath, string $pdfContent): void;

    public function read(string $relativePath): ?string;
}
