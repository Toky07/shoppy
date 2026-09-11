<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Product\Domain\ValueObject\ProductId;
use App\Shared\Domain\Exception\NotFoundException;

final class ProductNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(ProductId $id)
    {
        parent::__construct(sprintf('Product "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'product_not_found';
    }
}
