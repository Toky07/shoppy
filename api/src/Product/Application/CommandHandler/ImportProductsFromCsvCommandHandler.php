<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\Csv\ProductCsvReader;
use App\Product\Application\Csv\ProductCsvRow;
use App\Product\Application\Image\ProductImageWriter;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductName;

final readonly class ImportProductsFromCsvCommandHandler
{
    public function __construct(
        private ProductCsvReader $csvReader,
        private ProductImageWriter $imageWriter,
        private CreateProductCommandHandler $createProduct,
        private ProductRepository $productRepository,
    ) {
    }

    public function handle(ImportProductsFromCsvCommand $command): int
    {
        $imported = 0;

        foreach ($this->csvReader->read($command->csvPath) as $row) {
            if ($this->productRepository->findByName(ProductName::fromString($row->name)) !== null) {
                continue;
            }

            $this->createProduct->handle(new CreateProductCommand(
                name: $row->name,
                priceCents: $row->priceCents,
                description: $row->description,
                stock: $row->stock,
                imageUrl: $this->imageUrl($row),
            ));
            ++$imported;
        }

        return $imported;
    }

    private function imageUrl(ProductCsvRow $row): string
    {
        if (str_starts_with($row->image, 'http://') || str_starts_with($row->image, 'https://')) {
            return $row->image;
        }

        $slug = basename($row->image);

        return $this->imageWriter->write($slug, $row->name);
    }
}
