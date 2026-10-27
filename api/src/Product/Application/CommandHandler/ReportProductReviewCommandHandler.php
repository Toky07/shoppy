<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\ReportProductReviewCommand;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\CannotReportOwnReview;
use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Exception\ReviewNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewId;

final readonly class ReportProductReviewCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductReviewRepository $reviewRepository,
    ) {
    }

    public function handle(ReportProductReviewCommand $command): ProductReview
    {
        $product = $this->findPublished($command->productId);

        if ($product === null) {
            throw new ProductNotFound($command->productId);
        }

        $review = $this->reviewRepository->findById(ReviewId::fromString($command->reviewId));

        if ($review === null || $review->productId()->value() !== $product->id()->value()) {
            throw new ReviewNotFound($command->reviewId);
        }

        $reporterId = ReviewAuthorId::fromString($command->reporterId);

        if ($review->authorId()->value() === $reporterId->value()) {
            throw new CannotReportOwnReview();
        }

        $review->report();
        $this->reviewRepository->save($review);

        return $review;
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
