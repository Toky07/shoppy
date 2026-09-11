<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Order\Domain\ValueObject\CatalogProductId;
use App\Shared\Domain\Exception\NotFoundException;

final class CatalogProductNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(CatalogProductId $id)
    {
        parent::__construct(sprintf('Catalog product "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'product_not_found';
    }
}
