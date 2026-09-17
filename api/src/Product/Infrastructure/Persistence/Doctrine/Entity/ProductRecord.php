<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\StockQuantity;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'uniq_products_slug', columns: ['slug'])]
class ProductRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'price_cents')]
    private int $priceCents;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(Product $product): self
    {
        $record = new self();
        $record->id = $product->id()->value();
        $record->createdAt = $product->createdAt();
        $record->syncFromDomain($product);

        return $record;
    }

    public function updateFromDomain(Product $product): void
    {
        $this->syncFromDomain($product);
    }

    public function toDomain(): Product
    {
        return Product::create(
            ProductId::fromString($this->id),
            ProductName::fromString($this->name),
            ProductPrice::fromCents($this->priceCents),
            $this->createdAt,
            $this->description === null ? null : ProductDescription::fromString($this->description),
            StockQuantity::fromInt($this->stock),
            ProductSlug::fromString($this->slug),
        );
    }

    private function syncFromDomain(Product $product): void
    {
        $this->name = $product->name()->value();
        $this->slug = $product->slug()->value();
        $this->description = $product->description()?->value();
        $this->priceCents = $product->price()->cents();
        $this->currency = $product->price()->currency();
        $this->stock = $product->stock()->value();
    }
}
