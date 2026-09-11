<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductImage;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class ProductRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'price_cents')]
    private int $priceCents;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column(name: 'image_url', length: 2048, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(Product $product): self
    {
        $record = new self();
        $record->apply($product);

        return $record;
    }

    public function updateFromDomain(Product $product): void
    {
        $this->name = $product->name()->value();
        $this->description = $product->description()?->value();
        $this->priceCents = $product->price()->cents();
        $this->currency = $product->price()->currency();
        $this->stock = $product->stock()->value();
        $this->imageUrl = $product->image()?->value();
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
            $this->imageUrl === null ? null : ProductImage::fromString($this->imageUrl),
        );
    }

    private function apply(Product $product): void
    {
        $this->id = $product->id()->value();
        $this->name = $product->name()->value();
        $this->description = $product->description()?->value();
        $this->priceCents = $product->price()->cents();
        $this->currency = $product->price()->currency();
        $this->stock = $product->stock()->value();
        $this->imageUrl = $product->image()?->value();
        $this->createdAt = $product->createdAt();
    }
}
