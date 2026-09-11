<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductSort extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product list sort must be valid.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_sort';
    }

    public function field(): string
    {
        return 'sort';
    }
}
