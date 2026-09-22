<?php

declare(strict_types=1);

namespace App\Payment\Application\Port;

interface PayableOrder
{
    public function isPending(string $orderId): bool;
}
