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
use App\Product\Domain\Exception\InvalidReviewPagination;
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
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListProductReviewsQuery::MAX_LIMIT) {
            throw new InvalidReviewPagination();
        }

        $product = $this->findPublished($query->productId);

        if ($product === null) {
            throw new ProductNotFound($query->productId);
        }

        $offset = ($query->page - 1) * $query->limit;
        $reviews = $this->reviewRepository->findPageByProduct($product->id(), $offset, $query->limit);
        $labels = $this->reviewAuthors->labelsFor(array_map(
            static fn (ProductReview $review): string => $review->authorId()->value(),
            $reviews,
        ));
        $viewerId = $query->viewerId === null ? null : strtolower($query->viewerId);
        $count = $this->reviewRepository->countByProduct($product->id());
        $average = $this->reviewRepository->averageRatingByProduct($product->id());

        $items = array_map(
            static fn (ProductReview $review): ProductReviewResponse => ProductReviewResponse::fromReview(
                $review,
                $labels[$review->authorId()->value()] ?? 'Client',
                $viewerId !== null && $review->authorId()->value() === $viewerId,
            ),
            $reviews,
        );

        return new ProductReviewListResponse(
            $items,
            $query->page,
            $query->limit,
            $count,
            $count === 0 || $average === null ? null : round($average, 1),
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
