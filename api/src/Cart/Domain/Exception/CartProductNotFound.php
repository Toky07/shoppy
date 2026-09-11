<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Cart\Domain\ValueObject\CartProductId;
use App\Shared\Domain\Exception\NotFoundException;

final class CartProductNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(CartProductId $id)
    {
        parent::__construct(sprintf('Catalog product "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'product_not_found';
    }
}
