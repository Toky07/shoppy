<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

final readonly class MarkOrderPaidCommand
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
