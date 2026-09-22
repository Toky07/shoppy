<?php

declare(strict_types=1);

namespace App\Product\Application\Query;

final readonly class ListRelatedProductsQuery
{
    public const LIMIT = 4;

    public function __construct(public string $productId)
    {
    }
}
