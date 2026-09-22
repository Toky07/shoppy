<?php

declare(strict_types=1);

namespace App\Order\Application\Port;

use App\Order\Domain\ValueObject\CustomerId;

interface CustomerContact
{
    public function emailFor(CustomerId $customerId): ?string;
}
