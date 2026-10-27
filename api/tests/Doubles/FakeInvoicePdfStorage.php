<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Invoice\Application\Port\InvoicePdfStorage;

final class FakeInvoicePdfStorage implements InvoicePdfStorage
{
    /** @var array<string, string> */
    public array $files = [];

    public function store(string $relativePath, string $pdfContent): void
    {
        $this->files[$relativePath] = $pdfContent;
    }

    public function read(string $relativePath): ?string
    {
        return $this->files[$relativePath] ?? null;
    }
}
