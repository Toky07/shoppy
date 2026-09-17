<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductSlug extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product slug must be a lowercase hyphenated identifier.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_slug';
    }

    public function field(): string
    {
        return 'slug';
    }
}
