<?php

declare(strict_types=1);

namespace App\Payment\Application\Port;

use App\Payment\Domain\ValueObject\CustomerReference;

interface PayerContact
{
    public function emailFor(CustomerReference $customerId): ?string;
}
