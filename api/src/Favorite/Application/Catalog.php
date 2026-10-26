<?php

declare(strict_types=1);

namespace App\Favorite\Application;

use App\Favorite\Domain\ValueObject\FavoriteProductId;

interface Catalog
{
    public function isPublished(FavoriteProductId $id): bool;

    /**
     * @param list<FavoriteProductId> $ids
     *
     * @return list<FavoriteProductId>
     */
    public function publishedAmong(array $ids): array;
}
