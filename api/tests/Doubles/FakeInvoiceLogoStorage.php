<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Invoice\Application\Port\InvoiceLogoStorage;

final class FakeInvoiceLogoStorage implements InvoiceLogoStorage
{
    /** @var array<string, string> */
    public array $files = [];

    public function store(string $extension, string $binaryContent): string
    {
        $path = 'invoices/logos/shop.'.$extension;
        $this->files[$path] = $binaryContent;

        return $path;
    }
}
