<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidShippingMethod extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Shipping method is not available.');
    }

    public function errorCode(): string
    {
        return 'invalid_shipping_method';
    }

    public function field(): string
    {
        return 'shippingMethod';
    }
}
