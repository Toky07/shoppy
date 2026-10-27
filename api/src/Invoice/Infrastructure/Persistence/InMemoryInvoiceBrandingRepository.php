<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence;

use App\Invoice\Domain\Entity\InvoiceBranding;
use App\Invoice\Domain\Repository\InvoiceBrandingRepository;

final class InMemoryInvoiceBrandingRepository implements InvoiceBrandingRepository
{
    public function __construct(private InvoiceBranding $branding = new InvoiceBranding())
    {
    }

    public function get(): InvoiceBranding
    {
        return $this->branding;
    }

    public function save(InvoiceBranding $branding): void
    {
        $this->branding = $branding;
    }
}
