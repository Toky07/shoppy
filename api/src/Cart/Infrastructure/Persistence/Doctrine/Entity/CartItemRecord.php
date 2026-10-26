<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine\Entity;

use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CartVariantId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
#[ORM\UniqueConstraint(name: 'uniq_cart_items_cart_product_variant', columns: ['cart_id', 'product_id', 'variant_id'])]
#[ORM\Index(name: 'idx_cart_items_product_id', columns: ['product_id'])]
class CartItemRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CartRecord::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', nullable: false, onDelete: 'CASCADE')]
    private CartRecord $cart;

    #[ORM\Column(name: 'product_id', length: 36)]
    private string $productId;

    #[ORM\Column(name: 'variant_id', length: 36, nullable: true)]
    private ?string $variantId = null;

    #[ORM\Column]
    private int $quantity;

    #[ORM\Column]
    private int $position;

    public static function fromDomain(CartRecord $cart, CartItem $item, int $position): self
    {
        $record = new self();
        $record->cart = $cart;
        $record->productId = $item->productId()->value();
        $record->variantId = $item->variantId()?->value();
        $record->updateFromDomain($item, $position);

        return $record;
    }

    public function updateFromDomain(CartItem $item, int $position): void
    {
        $this->quantity = $item->quantity()->value();
        $this->position = $position;
    }

    public function lineKey(): string
    {
        return $this->productId."\0".($this->variantId ?? '');
    }

    public function toDomain(): CartItem
    {
        return CartItem::of(
            CartProductId::fromString($this->productId),
            CartQuantity::fromInt($this->quantity),
            $this->variantId === null ? null : CartVariantId::fromString($this->variantId),
        );
    }
}
