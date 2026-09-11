<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\Exception\InsufficientProductStock;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductImage;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use DateTimeImmutable;

final class Product
{
    private function __construct(
        private ProductId $id,
        private ProductName $name,
        private ProductPrice $price,
        private DateTimeImmutable $createdAt,
        private ?ProductDescription $description,
        private StockQuantity $stock,
        private ?ProductImage $image,
    ) {
    }

    public static function create(
        ProductId $id,
        ProductName $name,
        ProductPrice $price,
        DateTimeImmutable $createdAt,
        ?ProductDescription $description = null,
        ?StockQuantity $stock = null,
        ?ProductImage $image = null,
    ): self {
        return new self($id, $name, $price, $createdAt, $description, $stock ?? StockQuantity::zero(), $image);
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function price(): ProductPrice
    {
        return $this->price;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function description(): ?ProductDescription
    {
        return $this->description;
    }

    public function stock(): StockQuantity
    {
        return $this->stock;
    }

    public function image(): ?ProductImage
    {
        return $this->image;
    }

    public function rename(ProductName $name): void
    {
        $this->name = $name;
    }

    public function changePrice(ProductPrice $price): void
    {
        $this->price = $price;
    }

    public function changeDescription(?ProductDescription $description): void
    {
        $this->description = $description;
    }

    public function setStock(StockQuantity $stock): void
    {
        $this->stock = $stock;
    }

    public function decreaseStock(int $quantity): void
    {
        if (!$this->stock->isAtLeast($quantity)) {
            throw new InsufficientProductStock($this->stock->value(), $quantity);
        }

        $this->stock = $this->stock->subtract($quantity);
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity < 1) {
            return;
        }

        $this->stock = $this->stock->add($quantity);
    }
}
