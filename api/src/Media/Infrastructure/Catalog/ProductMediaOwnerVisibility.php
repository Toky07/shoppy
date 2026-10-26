<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Catalog;

use App\Media\Application\Port\MediaOwnerVisibility;
use App\Media\Domain\Exception\InvalidMediaOwnerType;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;

final readonly class ProductMediaOwnerVisibility implements MediaOwnerVisibility
{
    public function __construct(private ProductRepository $products)
    {
    }

    public function isPubliclyListable(string $ownerType, string $ownerId): bool
    {
        try {
            $type = MediaOwnerType::fromInput($ownerType);
        } catch (InvalidMediaOwnerType) {
            return false;
        }

        if ($type->alias() !== MediaOwnerType::PRODUCT_ALIAS) {
            return false;
        }

        if (!ProductId::isValid($ownerId)) {
            return false;
        }

        $product = $this->products->findById(ProductId::fromString($ownerId));

        return $product !== null && $product->isPublished();
    }
}
