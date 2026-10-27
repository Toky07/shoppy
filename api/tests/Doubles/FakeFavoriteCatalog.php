<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Favorite\Application\Catalog;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final class FakeFavoriteCatalog implements Catalog
{
    /** @var array<string, true> */
    private array $published = [];

    public int $publishedAmongCalls = 0;

    public function publish(string $productId): void
    {
        $this->published[$productId] = true;
    }

    public function isPublished(FavoriteProductId $id): bool
    {
        return isset($this->published[$id->value()]);
    }

    public function publishedAmong(array $ids): array
    {
        $this->publishedAmongCalls++;
        $published = [];

        foreach ($ids as $id) {
            if ($this->isPublished($id)) {
                $published[] = $id;
            }
        }

        return $published;
    }
}
