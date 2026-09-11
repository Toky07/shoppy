<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductPrice extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product price cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_price';
    }

    public function field(): string
    {
        return 'priceCents';
    }
}
