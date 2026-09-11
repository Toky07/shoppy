<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Cart\Domain\ValueObject\CartProductId;
use App\Shared\Domain\Exception\NotFoundException;

final class CartItemNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(CartProductId $productId)
    {
        parent::__construct(sprintf('Cart item "%s" was not found.', $productId->value()));
    }

    public function errorCode(): string
    {
        return 'cart_item_not_found';
    }
}
