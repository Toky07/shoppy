<?php

declare(strict_types=1);

namespace App\Favorite\Infrastructure\Persistence;

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;

final class InMemoryFavoriteListRepository implements FavoriteListRepository
{
    /** @var array<string, FavoriteList> */
    private array $byCustomerId = [];

    public int $saveCalls = 0;

    public function save(FavoriteList $list): void
    {
        $this->saveCalls++;
        $this->byCustomerId[$list->customerId()->value()] = $list;
    }

    public function findByCustomerId(CustomerId $customerId): ?FavoriteList
    {
        $list = $this->byCustomerId[$customerId->value()] ?? null;

        return $list === null ? null : clone $list;
    }
}
