<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidOrderStatus extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Order status must be pending, cancelled, or paid.');
    }

    public function errorCode(): string
    {
        return 'invalid_order_status';
    }

    public function field(): string
    {
        return 'status';
    }
}
