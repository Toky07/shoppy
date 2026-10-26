<?php

declare(strict_types=1);

namespace App\Favorite\Application\CommandHandler;

use App\Favorite\Application\Command\ClearFavoritesCommand;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;

final readonly class ClearFavoritesCommandHandler
{
    public function __construct(private FavoriteListRepository $favorites)
    {
    }

    public function handle(ClearFavoritesCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $list = $this->favorites->findByCustomerId($customerId);

        if ($list === null) {
            return;
        }

        $list->clear();
        $this->favorites->save($list);
    }
}
