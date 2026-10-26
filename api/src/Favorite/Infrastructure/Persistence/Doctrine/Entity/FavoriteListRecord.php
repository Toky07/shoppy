<?php

declare(strict_types=1);

namespace App\Favorite\Infrastructure\Persistence\Doctrine\Entity;

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'favorite_lists')]
class FavoriteListRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    /** @var Collection<int, FavoriteItemRecord> */
    #[ORM\OneToMany(targetEntity: FavoriteItemRecord::class, mappedBy: 'list', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public static function fromDomain(FavoriteList $list): self
    {
        $record = new self();
        $record->customerId = $list->customerId()->value();
        $record->syncFromDomain($list);

        return $record;
    }

    public function updateFromDomain(FavoriteList $list): void
    {
        $this->syncFromDomain($list);
    }

    public function toDomain(): FavoriteList
    {
        $items = $this->items->toArray();
        usort(
            $items,
            static fn (FavoriteItemRecord $left, FavoriteItemRecord $right): int => $left->position() <=> $right->position(),
        );

        return FavoriteList::reconstitute(
            CustomerId::fromString($this->customerId),
            array_map(
                static fn (FavoriteItemRecord $item): FavoriteProductId => $item->toDomain(),
                $items,
            ),
        );
    }

    private function syncFromDomain(FavoriteList $list): void
    {
        $indexed = [];

        foreach ($this->items as $record) {
            $indexed[$record->productId()] = $record;
        }

        $seen = [];

        foreach ($list->productIds() as $position => $productId) {
            $key = $productId->value();
            $seen[$key] = true;
            $existing = $indexed[$key] ?? null;

            if ($existing === null) {
                $this->items->add(FavoriteItemRecord::fromDomain($this, $productId, $position));
                continue;
            }

            $existing->updatePosition($position);
        }

        foreach ($this->items->toArray() as $record) {
            if (!isset($seen[$record->productId()])) {
                $this->items->removeElement($record);
            }
        }
    }
}
