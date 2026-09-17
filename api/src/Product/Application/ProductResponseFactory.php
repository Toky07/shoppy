<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Product\Application\Response\ProductResponse;
use App\Product\Domain\Entity\Product;

final readonly class ProductResponseFactory
{
    public function __construct(private MediaRepository $mediaRepository)
    {
    }

    public function fromProduct(Product $product): ProductResponse
    {
        $items = $this->mediaRepository->findByOwner(
            MediaOwnerType::fromInput(MediaOwnerType::PRODUCT_ALIAS),
            MediaOwnerId::fromString($product->id()->value()),
        );

        return ProductResponse::fromProduct(
            $product,
            array_map(static fn (Media $media): string => $media->publicUrl(), $items),
        );
    }
}
