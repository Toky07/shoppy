<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductImage extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Product image must be a media path or an http(s) URL.');
    }

    public function errorCode(): string
    {
        return 'invalid_product_image';
    }

    public function field(): string
    {
        return 'imageUrl';
    }
}
