<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Domain\Exception\NotFoundException;

final class OrderNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(OrderId $id)
    {
        parent::__construct(sprintf('Order "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'order_not_found';
    }
}
