<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine\Entity;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CustomerId;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'carts')]
#[ORM\UniqueConstraint(name: 'uniq_carts_customer_id', columns: ['customer_id'])]
class CartRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'integer')]
    private int $version = 1;

    /** @var Collection<int, CartItemRecord> */
    #[ORM\OneToMany(targetEntity: CartItemRecord::class, mappedBy: 'cart', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public static function fromDomain(Cart $cart): self
    {
        $record = new self();
        $record->id = $cart->id()->value();
        $record->customerId = $cart->customerId()->value();
        $record->syncFromDomain($cart);

        return $record;
    }

    public function updateFromDomain(Cart $cart): void
    {
        $this->updatedAt = $cart->updatedAt();
        $this->version = $cart->version();
        $this->items->clear();
    }

    public function syncVersion(int $version): void
    {
        $this->version = $version;
    }

    /**
     * @param list<CartItem> $items
     */
    public function addItems(array $items): void
    {
        foreach ($items as $position => $item) {
            $this->items->add(CartItemRecord::fromDomain($this, $item, $position));
        }
    }

    public function toDomain(): Cart
    {
        return Cart::reconstitute(
            CartId::fromString($this->id),
            CustomerId::fromString($this->customerId),
            array_values($this->items->map(
                static fn (CartItemRecord $item): CartItem => $item->toDomain(),
            )->toArray()),
            $this->updatedAt,
            $this->version,
        );
    }

    private function syncFromDomain(Cart $cart): void
    {
        $this->updatedAt = $cart->updatedAt();
        $this->version = $cart->version();
        $this->addItems($cart->items());
    }
}
