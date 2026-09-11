<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidOrderReference extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Order id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_order_id';
    }

    public function field(): string
    {
        return 'orderId';
    }
}
