<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductSku extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('SKU must be an uppercase code, for example NUVORA-TEE.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_sku';
    }

    public function field(): string
    {
        return 'sku';
    }
}
