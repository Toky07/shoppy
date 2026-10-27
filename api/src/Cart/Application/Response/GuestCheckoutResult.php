<?php

declare(strict_types=1);

namespace App\Cart\Application\Response;

use App\Auth\Application\Response\IssuedSession;
use App\Order\Domain\ValueObject\OrderId;

final readonly class GuestCheckoutResult
{
    public function __construct(
        public OrderId $orderId,
        public IssuedSession $session,
    ) {
    }
}
