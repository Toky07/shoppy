<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\Entity\ProductVariant;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\StockQuantity;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'uniq_products_slug', columns: ['slug'])]
#[ORM\UniqueConstraint(name: 'uniq_products_sku', columns: ['sku'])]
#[ORM\Index(name: 'idx_products_price_cents', columns: ['price_cents'])]
#[ORM\Index(name: 'idx_products_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_products_name', columns: ['name'])]
#[ORM\Index(name: 'idx_products_category_id', columns: ['category_id'])]
#[ORM\Index(name: 'idx_products_published', columns: ['published'])]
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

    #[ORM\Column(name: 'category_id', length: 36, nullable: true)]
    private ?string $categoryId = null;

    #[ORM\Column(length: 40)]
    private string $sku;

    #[ORM\Column]
    private bool $published = true;

    /** @var Collection<int, ProductVariantRecord> */
    #[ORM\OneToMany(targetEntity: ProductVariantRecord::class, mappedBy: 'product', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $variants;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
    }

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
            $this->categoryId === null ? null : CategoryId::fromString($this->categoryId),
            ProductSku::fromString($this->sku),
            array_map(
                static fn (ProductVariantRecord $record): ProductVariant => $record->toDomain(),
                $this->variants->getValues(),
            ),
            $this->published,
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
        $this->categoryId = $product->categoryId()?->value();
        $this->sku = $product->sku()->value();
        $this->published = $product->isPublished();
        $this->syncVariants($product);
    }

    private function syncVariants(Product $product): void
    {
        $indexed = [];

        foreach ($this->variants as $record) {
            $indexed[$record->id()] = $record;
        }

        $seen = [];

        foreach ($product->variants() as $position => $variant) {
            $id = $variant->id()->value();
            $seen[$id] = true;
            $existing = $indexed[$id] ?? null;

            if ($existing === null) {
                $this->variants->add(ProductVariantRecord::fromDomain($this, $variant, $position));
                continue;
            }

            $existing->updateFromDomain($variant, $position);
        }

        foreach ($this->variants->toArray() as $record) {
            if (!isset($seen[$record->id()])) {
                $this->variants->removeElement($record);
            }
        }
    }
}
