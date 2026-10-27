<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Audit\Application\Port\AuditLogger;
use App\Audit\Domain\ValueObject\AuditAction;
use App\Audit\Domain\ValueObject\AuditSubjectType;
use App\Product\Application\Command\SetProductStockCommand;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\StockQuantity;

final readonly class SetProductStockCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ?AuditLogger $audit = null,
    ) {
    }

    public function handle(SetProductStockCommand $command): void
    {
        $id = ProductId::fromString($command->id);
        $product = $this->productRepository->findById($id);

        if ($product === null) {
            throw new ProductNotFound($id->value());
        }

        $previous = $product->stock()->value();
        $product->setStock(StockQuantity::fromInt($command->stock));
        $this->productRepository->save($product);

        if ($previous !== $command->stock) {
            $this->audit?->record(AuditAction::STOCK_CHANGED, AuditSubjectType::PRODUCT, $id->value(), [
                'from' => $previous,
                'to' => $command->stock,
            ]);
        }
    }
}
