<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\ProductVariant;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Product\Domain\ValueObject\VariantId;
use App\Product\Domain\ValueObject\VariantLabel;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_variants')]
#[ORM\UniqueConstraint(name: 'uniq_product_variants_sku', columns: ['sku'])]
#[ORM\UniqueConstraint(name: 'uniq_product_variants_options', columns: ['product_id', 'size', 'color'])]
#[ORM\Index(name: 'idx_product_variants_product_id', columns: ['product_id'])]
class ProductVariantRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: ProductRecord::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'product_id', nullable: false, onDelete: 'CASCADE')]
    private ProductRecord $product;

    #[ORM\Column(length: 40)]
    private string $sku;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $size = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $color = null;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column]
    private int $position = 0;

    public static function fromDomain(ProductRecord $product, ProductVariant $variant, int $position): self
    {
        $record = new self();
        $record->product = $product;
        $record->id = $variant->id()->value();
        $record->updateFromDomain($variant, $position);

        return $record;
    }

    public function updateFromDomain(ProductVariant $variant, int $position): void
    {
        $this->sku = $variant->sku()->value();
        $this->size = $variant->size()?->value();
        $this->color = $variant->color()?->value();
        $this->stock = $variant->stock()->value();
        $this->position = $position;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function product(): ProductRecord
    {
        return $this->product;
    }

    public function toDomain(): ProductVariant
    {
        return ProductVariant::create(
            VariantId::fromString($this->id),
            ProductSku::fromString($this->sku),
            $this->size === null ? null : VariantLabel::fromString($this->size, 'size'),
            $this->color === null ? null : VariantLabel::fromString($this->color, 'color'),
            StockQuantity::fromInt($this->stock),
        );
    }
}
