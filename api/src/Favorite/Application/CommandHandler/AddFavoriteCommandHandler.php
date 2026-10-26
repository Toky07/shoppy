<?php

declare(strict_types=1);

namespace App\Favorite\Application\CommandHandler;

use App\Favorite\Application\Catalog;
use App\Favorite\Application\Command\AddFavoriteCommand;
use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\Exception\FavoriteProductNotFound;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final readonly class AddFavoriteCommandHandler
{
    public function __construct(
        private FavoriteListRepository $favorites,
        private Catalog $catalog,
    ) {
    }

    public function handle(AddFavoriteCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $productId = FavoriteProductId::fromString($command->productId);

        if (!$this->catalog->isPublished($productId)) {
            throw new FavoriteProductNotFound($productId);
        }

        $list = $this->favorites->findByCustomerId($customerId) ?? FavoriteList::empty($customerId);
        $list->add($productId);
        $this->favorites->save($list);
    }
}
