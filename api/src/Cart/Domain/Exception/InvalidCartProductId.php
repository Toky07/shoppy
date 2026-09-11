<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidCartProductId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_cart_product_id';
    }

    public function field(): string
    {
        return 'productId';
    }
}
