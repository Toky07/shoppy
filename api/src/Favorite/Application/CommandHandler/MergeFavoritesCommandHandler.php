<?php

declare(strict_types=1);

namespace App\Favorite\Application\CommandHandler;

use App\Favorite\Application\Catalog;
use App\Favorite\Application\Command\MergeFavoritesCommand;
use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final readonly class MergeFavoritesCommandHandler
{
    public function __construct(
        private FavoriteListRepository $favorites,
        private Catalog $catalog,
    ) {
    }

    public function handle(MergeFavoritesCommand $command): void
    {
        $incoming = [];

        foreach ($command->productIds as $productId) {
            $incoming[] = FavoriteProductId::fromString($productId);
        }

        $published = $this->catalog->publishedAmong($incoming);

        if ($published === []) {
            return;
        }

        $customerId = CustomerId::fromString($command->customerId);
        $list = $this->favorites->findByCustomerId($customerId) ?? FavoriteList::empty($customerId);
        $list->merge($published);
        $this->favorites->save($list);
    }
}
