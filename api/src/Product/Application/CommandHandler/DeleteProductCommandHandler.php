<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Media\Application\Command\DeleteMediaByOwnerCommand;
use App\Media\Application\CommandHandler\DeleteMediaByOwnerCommandHandler;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class DeleteProductCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private DeleteMediaByOwnerCommandHandler $deleteMediaByOwner,
    ) {
    }

    public function handle(DeleteProductCommand $command): void
    {
        $id = ProductId::fromString($command->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id->value());
        }

        $this->deleteMediaByOwner->handle(new DeleteMediaByOwnerCommand(
            MediaOwnerType::PRODUCT_ALIAS,
            $id->value(),
        ));
        $this->productRepository->delete($product);
    }
}
