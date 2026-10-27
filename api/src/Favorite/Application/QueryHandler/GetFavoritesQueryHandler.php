<?php

declare(strict_types=1);

namespace App\Favorite\Application\QueryHandler;

use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\Response\FavoriteListResponse;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;

final readonly class GetFavoritesQueryHandler
{
    public function __construct(private FavoriteListRepository $favorites)
    {
    }

    public function handle(GetFavoritesQuery $query): FavoriteListResponse
    {
        return FavoriteListResponse::fromList(
            $this->favorites->findByCustomerId(CustomerId::fromString($query->customerId)),
        );
    }
}
