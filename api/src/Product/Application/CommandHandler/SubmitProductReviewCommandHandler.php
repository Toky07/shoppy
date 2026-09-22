<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\SubmitProductReviewCommand;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Exception\InvalidProductSlug;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use App\Shared\Domain\Clock;

final readonly class SubmitProductReviewCommandHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductReviewRepository $reviewRepository,
        private Clock $clock,
    ) {
    }

    public function handle(SubmitProductReviewCommand $command): ProductReview
    {
        $product = $this->findPublished($command->productId);

        if ($product === null) {
            throw new ProductNotFound($command->productId);
        }

        $authorId = ReviewAuthorId::fromString($command->authorId);
        $rating = Rating::fromInt($command->rating);
        $body = ReviewBody::fromString($command->body);
        $existing = $this->reviewRepository->findByProductAndAuthor($product->id(), $authorId);

        if ($existing !== null) {
            $existing->revise($rating, $body);
            $this->reviewRepository->save($existing);

            return $existing;
        }

        $review = ProductReview::submit(
            ReviewId::generate(),
            $product->id(),
            $authorId,
            $rating,
            $body,
            $this->clock->now(),
        );
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
