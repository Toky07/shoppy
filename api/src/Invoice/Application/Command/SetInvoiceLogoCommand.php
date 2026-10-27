<?php

declare(strict_types=1);

namespace App\Invoice\Application\Command;

final readonly class SetInvoiceLogoCommand
{
    public function __construct(public string $binaryContent)
    {
    }
}
