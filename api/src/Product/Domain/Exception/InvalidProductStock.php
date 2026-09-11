<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductStock extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product stock cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_stock';
    }

    public function field(): string
    {
        return 'stock';
    }
}
