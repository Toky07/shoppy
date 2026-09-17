<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\UniqueProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;

final readonly class UpdateProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private UniqueProductSlug $uniqueProductSlug,
    ) {
    }

    public function handle(UpdateProductCommand $command): void
    {
        $id = ProductId::fromString($command->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id->value());
        }

        if ($command->name !== null) {
            $name = ProductName::fromString($command->name);
            $product->rename($name);
            $product->changeSlug($this->uniqueProductSlug->allocate($name, $id));
        }

        if ($command->priceCents !== null) {
            $product->changePrice(ProductPrice::fromCents($command->priceCents));
        }

        if ($command->descriptionProvided) {
            $product->changeDescription(
                $command->description === null ? null : ProductDescription::fromString($command->description),
            );
        }

        $this->productRepository->save($product);
    }
}
