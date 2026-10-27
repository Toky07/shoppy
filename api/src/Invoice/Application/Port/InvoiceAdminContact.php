<?php

declare(strict_types=1);

namespace App\Invoice\Application\Port;

interface InvoiceAdminContact
{
    /**
     * @return list<string>
     */
    public function emails(): array;
}
