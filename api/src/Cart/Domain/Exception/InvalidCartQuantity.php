<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidCartQuantity extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Quantity must be at least 1.');
    }

    public function errorCode(): string
    {
        return 'invalid_cart_quantity';
    }

    public function field(): string
    {
        return 'quantity';
    }
}
