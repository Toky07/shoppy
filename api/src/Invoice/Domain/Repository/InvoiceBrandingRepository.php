<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Repository;

use App\Invoice\Domain\Entity\InvoiceBranding;

interface InvoiceBrandingRepository
{
    public function get(): InvoiceBranding;

    public function save(InvoiceBranding $branding): void;
}
