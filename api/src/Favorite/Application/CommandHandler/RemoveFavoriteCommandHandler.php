<?php

declare(strict_types=1);

namespace App\Favorite\Application\CommandHandler;

use App\Favorite\Application\Command\RemoveFavoriteCommand;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final readonly class RemoveFavoriteCommandHandler
{
    public function __construct(private FavoriteListRepository $favorites)
    {
    }

    public function handle(RemoveFavoriteCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $list = $this->favorites->findByCustomerId($customerId);

        if ($list === null) {
            return;
        }

        $list->remove(FavoriteProductId::fromString($command->productId));
        $this->favorites->save($list);
    }
}
