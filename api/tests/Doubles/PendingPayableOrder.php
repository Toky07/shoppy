<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Application\Port\PayableOrder;

final class PendingPayableOrder implements PayableOrder
{
    public function __construct(private bool $pending = true)
    {
    }

    public function isPending(string $orderId): bool
    {
        return $this->pending;
    }
}
