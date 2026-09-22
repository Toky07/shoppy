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
        $record->quantity = $item->quantity()->value();
        $record->position = $position;

        return $record;
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
