<?php

declare(strict_types=1);

namespace App\Favorite\Domain\Exception;

use App\Favorite\Domain\ValueObject\FavoriteProductId;
use App\Shared\Domain\Exception\NotFoundException;

final class FavoriteProductNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(FavoriteProductId $id)
    {
        parent::__construct(sprintf('Catalog product "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'product_not_found';
    }
}
