<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductPriceFilter extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(private string $fieldName, string $message)
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'invalid_product_price_filter';
    }

    public function field(): string
    {
        return $this->fieldName;
    }
}
