<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductImage;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use App\Product\Domain\ValueObject\StockQuantity;
use App\Shared\Domain\Clock;

final readonly class CreateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private Clock $clock,
    ) {
    }

    public function handle(CreateProductCommand $command): ProductId
    {
        $product = Product::create(
            ProductId::generate(),
            ProductName::fromString($command->name),
            ProductPrice::fromCents($command->priceCents),
            $this->clock->now(),
            self::descriptionFrom($command->description),
            StockQuantity::fromInt($command->stock),
            self::imageFrom($command->imageUrl),
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

    private static function imageFrom(?string $imageUrl): ?ProductImage
    {
        if ($imageUrl === null) {
            return null;
        }

        return ProductImage::fromString($imageUrl);
    }
}
