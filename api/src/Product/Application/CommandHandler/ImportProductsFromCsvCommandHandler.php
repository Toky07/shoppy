<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\Command\SetProductStockCommand;
use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\Csv\ProductCsvReader;
use App\Product\Application\Image\ProductImageLoader;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductName;

final readonly class ImportProductsFromCsvCommandHandler
{
    public function __construct(
        private ProductCsvReader $csvReader,
        private ProductImageLoader $imageLoader,
        private CreateProductCommandHandler $createProduct,
        private ProductRepository $productRepository,
        private UploadMediaCommandHandler $uploadMedia,
        private UpdateProductCommandHandler $updateProduct,
        private SetProductStockCommandHandler $setStock,
    ) {
    }

    public function handle(ImportProductsFromCsvCommand $command): int
    {
        $imported = 0;

        foreach ($this->csvReader->read($command->csvPath) as $row) {
            $existing = $this->productRepository->findByName(ProductName::fromString($row->name));

            if ($existing !== null) {
                $this->updateProduct->handle(new UpdateProductCommand(
                    id: $existing->id()->value(),
                    priceCents: $row->priceCents,
                    descriptionProvided: true,
                    description: $row->description,
                ));

                if (!$existing->hasVariants()) {
                    $this->setStock->handle(new SetProductStockCommand(
                        $existing->id()->value(),
                        $row->stock,
                    ));
                }

                ++$imported;

                continue;
            }

            $productId = $this->createProduct->handle(new CreateProductCommand(
                name: $row->name,
                priceCents: $row->priceCents,
                description: $row->description,
                stock: $row->stock,
            ));

            foreach ($row->images as $position => $source) {
                $image = $this->imageLoader->load($source);
                $this->uploadMedia->handle(new UploadMediaCommand(
                    ownerType: MediaOwnerType::PRODUCT_ALIAS,
                    ownerId: $productId->value(),
                    originalFilename: $image->filename,
                    mimeType: $image->mimeType,
                    binaryContent: $image->contents,
                    position: $position,
                ));
            }

            ++$imported;
        }

        return $imported;
    }
}
