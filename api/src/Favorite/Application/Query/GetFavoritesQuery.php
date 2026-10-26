<?php

declare(strict_types=1);

namespace App\Favorite\Application\Query;

final readonly class GetFavoritesQuery
{
    public function __construct(public string $customerId)
    {
    }
}
