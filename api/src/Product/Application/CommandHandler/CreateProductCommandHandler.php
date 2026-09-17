<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Shared\Domain\Clock;

final readonly class CreateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private Clock $clock,
        private UniqueProductSlug $uniqueProductSlug,
    ) {
    }

    public function handle(CreateProductCommand $command): ProductId
    {
        $name = ProductName::fromString($command->name);
        $product = Product::create(
            ProductId::generate(),
            $name,
            ProductPrice::fromCents($command->priceCents),
            $this->clock->now(),
            self::descriptionFrom($command->description),
            StockQuantity::fromInt($command->stock),
            $this->uniqueProductSlug->allocate($name),
        );

        $this->productRepository->save($product);

        return $product->id();
    }

    private static function descriptionFrom(?string $description): ?ProductDescription
    {
        if ($description === null) {
            return null;
        }

        return ProductDescription::fromString($description);
    }
}
