<?php

declare(strict_types=1);

namespace App\Favorite\Infrastructure\Persistence\Doctrine\Entity;

use App\Favorite\Domain\ValueObject\FavoriteProductId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'favorite_items')]
#[ORM\UniqueConstraint(name: 'uniq_favorite_items_customer_product', columns: ['customer_id', 'product_id'])]
class FavoriteItemRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FavoriteListRecord::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'customer_id', nullable: false, onDelete: 'CASCADE')]
    private FavoriteListRecord $list;

    #[ORM\Column(name: 'product_id', length: 36)]
    private string $productId;

    #[ORM\Column]
    private int $position;

    public static function fromDomain(FavoriteListRecord $list, FavoriteProductId $productId, int $position): self
    {
        $record = new self();
        $record->list = $list;
        $record->productId = $productId->value();
        $record->position = $position;

        return $record;
    }

    public function updatePosition(int $position): void
    {
        $this->position = $position;
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function toDomain(): FavoriteProductId
    {
        return FavoriteProductId::fromString($this->productId);
    }
}
