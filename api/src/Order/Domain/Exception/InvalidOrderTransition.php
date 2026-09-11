<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Order\Domain\ValueObject\OrderStatus;
use App\Shared\Domain\Exception\ConflictException;

final class InvalidOrderTransition extends \RuntimeException implements ConflictException
{
    public function __construct(OrderStatus $from, string $to)
    {
        parent::__construct(sprintf(
            'Cannot transition order from "%s" to "%s".',
            $from->value(),
            $to,
        ));
    }

    public function errorCode(): string
    {
        return 'invalid_order_transition';
    }
}
