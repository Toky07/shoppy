<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductSearch extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product search must be at most 100 characters.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_search';
    }

    public function field(): string
    {
        return 'q';
    }
}
