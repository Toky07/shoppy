<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence;

use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewId;

final class InMemoryProductReviewRepository implements ProductReviewRepository
{
    /** @var array<string, ProductReview> */
    private array $reviews = [];

    public function save(ProductReview $review): void
    {
        $this->reviews[$review->id()->value()] = $review;
    }

    public function findById(ReviewId $id): ?ProductReview
    {
        return $this->reviews[$id->value()] ?? null;
    }

    public function findByProductAndAuthor(ProductId $productId, ReviewAuthorId $authorId): ?ProductReview
    {
        foreach ($this->reviews as $review) {
            if ($review->productId()->value() === $productId->value() && $review->authorId()->value() === $authorId->value()) {
                return $review;
            }
        }

        return null;
    }

    public function findByProduct(ProductId $productId): array
    {
        $matches = array_values(array_filter(
            $this->reviews,
            static fn (ProductReview $review): bool => $review->productId()->value() === $productId->value(),
        ));

        usort(
            $matches,
            static fn (ProductReview $left, ProductReview $right): int => $right->createdAt() <=> $left->createdAt(),
        );

        return $matches;
    }

    public function findPageByProduct(ProductId $productId, int $offset, int $limit): array
    {
        return array_values(array_slice($this->findByProduct($productId), $offset, $limit));
    }

    public function countByProduct(ProductId $productId): int
    {
        return count($this->findByProduct($productId));
    }

    public function averageRatingByProduct(ProductId $productId): ?float
    {
        $reviews = $this->findByProduct($productId);

        if ($reviews === []) {
            return null;
        }

        $total = 0;

        foreach ($reviews as $review) {
            $total += $review->rating()->value();
        }

        return $total / count($reviews);
    }
}
