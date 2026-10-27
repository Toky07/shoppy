<?php

declare(strict_types=1);

namespace App\Favorite\Application\Response;

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

final readonly class FavoriteListResponse
{
    /**
     * @param list<string> $productIds
     */
    public function __construct(public array $productIds)
    {
    }

    public static function fromList(?FavoriteList $list): self
    {
        if ($list === null) {
            return new self([]);
        }

        return new self(array_map(
            static fn (FavoriteProductId $id): string => $id->value(),
            $list->productIds(),
        ));
    }

    /**
     * @return array{productIds: list<string>}
     */
    public function toArray(): array
    {
        return ['productIds' => $this->productIds];
    }
}
