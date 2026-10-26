<?php

declare(strict_types=1);

namespace App\Product\Domain\Repository;

use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewId;

interface ProductReviewRepository
{
    public function save(ProductReview $review): void;

    public function findById(ReviewId $id): ?ProductReview;

    public function findByProductAndAuthor(ProductId $productId, ReviewAuthorId $authorId): ?ProductReview;

    /**
     * @return list<ProductReview>
     */
    public function findByProduct(ProductId $productId): array;

    /**
     * @return list<ProductReview>
     */
    public function findPageByProduct(ProductId $productId, int $offset, int $limit): array;

    public function countByProduct(ProductId $productId): int;

    public function averageRatingByProduct(ProductId $productId): ?float;
}
