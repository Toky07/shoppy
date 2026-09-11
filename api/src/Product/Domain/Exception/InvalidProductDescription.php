<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductDescription extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product description cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_description';
    }

    public function field(): string
    {
        return 'description';
    }
}
