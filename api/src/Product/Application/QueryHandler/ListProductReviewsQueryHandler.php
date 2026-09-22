<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\Response\ProductReviewListResponse;
use App\Product\Application\Response\ProductReviewResponse;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductSlug;

final readonly class ListProductReviewsQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductReviewRepository $reviewRepository,
        private ReviewAuthors $reviewAuthors,
    ) {
    }

    public function handle(ListProductReviewsQuery $query): ProductReviewListResponse
    {
        $product = $this->findPublished($query->productId);

        if ($product === null) {
            throw new ProductNotFound($query->productId);
        }

        $reviews = $this->reviewRepository->findByProduct($product->id());
        $labels = $this->reviewAuthors->labelsFor(array_map(
            static fn (ProductReview $review): string => $review->authorId()->value(),
            $reviews,
        ));
        $viewerId = $query->viewerId === null ? null : strtolower($query->viewerId);
        $total = 0;

        $items = array_map(
            static function (ProductReview $review) use ($labels, $viewerId, &$total): ProductReviewResponse {
                $total += $review->rating()->value();

                return ProductReviewResponse::fromReview(
                    $review,
                    $labels[$review->authorId()->value()] ?? 'Client',
                    $viewerId !== null && $review->authorId()->value() === $viewerId,
                );
            },
            $reviews,
        );

        $count = count($items);

        return new ProductReviewListResponse(
            $items,
            $count,
            $count === 0 ? null : round($total / $count, 1),
        );
    }

    private function findPublished(string $reference): ?Product
    {
        $product = null;

        if (ProductId::isValid($reference)) {
            $product = $this->productRepository->findById(ProductId::fromString($reference));
        }

        if ($product === null) {
            try {
                $product = $this->productRepository->findBySlug(ProductSlug::fromString($reference));
            } catch (InvalidProductSlug) {
                $product = null;
            }
        }

        if ($product === null || !$product->isPublished()) {
            return null;
        }

        return $product;
    }
}
