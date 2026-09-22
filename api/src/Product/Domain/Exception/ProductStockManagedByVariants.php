<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class ProductStockManagedByVariants extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Stock is defined by the variants.');
    }

    public function errorCode(): string
    {
        return 'stock_managed_by_variants';
    }

    public function field(): string
    {
        return 'stock';
    }
}
