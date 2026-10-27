<?php

declare(strict_types=1);

namespace App\Invoice\Application\Port;

interface InvoiceLogoStorage
{
    public function store(string $extension, string $binaryContent): string;
}
