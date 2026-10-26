<?php

declare(strict_types=1);

namespace App\Favorite\Domain\Repository;

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\ValueObject\CustomerId;

interface FavoriteListRepository
{
    public function save(FavoriteList $list): void;

    public function findByCustomerId(CustomerId $customerId): ?FavoriteList;
}
