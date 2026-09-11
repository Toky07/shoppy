<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class DeleteProductCommandHandler
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function handle(DeleteProductCommand $command): void
    {
        $id = ProductId::fromString($command->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id);
        }

        $this->productRepository->delete($product);
    }
}
