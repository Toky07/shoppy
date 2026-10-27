<?php

declare(strict_types=1);

namespace App\Favorite\Domain\Entity;

use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final class FavoriteList
{
    /**
     * @param list<FavoriteProductId> $productIds
     */
    private function __construct(
        private CustomerId $customerId,
        private array $productIds,
    ) {
    }

    public static function empty(CustomerId $customerId): self
    {
        return new self($customerId, []);
    }

    /**
     * @param list<FavoriteProductId> $productIds
     */
    public static function reconstitute(CustomerId $customerId, array $productIds): self
    {
        return new self($customerId, array_values($productIds));
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return list<FavoriteProductId>
     */
    public function productIds(): array
    {
        return $this->productIds;
    }

    public function contains(FavoriteProductId $productId): bool
    {
        foreach ($this->productIds as $id) {
            if ($id->equals($productId)) {
                return true;
            }
        }

        return false;
    }

    public function add(FavoriteProductId $productId): void
    {
        if ($this->contains($productId)) {
            return;
        }

        array_unshift($this->productIds, $productId);
    }

    public function remove(FavoriteProductId $productId): void
    {
        $this->productIds = array_values(array_filter(
            $this->productIds,
            static fn (FavoriteProductId $id): bool => !$id->equals($productId),
        ));
    }

    public function clear(): void
    {
        $this->productIds = [];
    }

    /**
     * @param list<FavoriteProductId> $incoming
     */
    public function merge(array $incoming): void
    {
        $prepended = [];
        $seen = [];

        foreach ($incoming as $id) {
            if (isset($seen[$id->value()])) {
                continue;
            }

            $seen[$id->value()] = true;
            $prepended[] = $id;
        }

        $rest = [];

        foreach ($this->productIds as $id) {
            if (!isset($seen[$id->value()])) {
                $rest[] = $id;
            }
        }

        $this->productIds = [...$prepended, ...$rest];
    }
}
