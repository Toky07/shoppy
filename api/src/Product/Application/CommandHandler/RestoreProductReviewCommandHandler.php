<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\RestoreProductReviewCommand;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\ReviewNotFound;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ReviewId;

final readonly class RestoreProductReviewCommandHandler
{
    public function __construct(private ProductReviewRepository $reviewRepository)
    {
    }

    public function handle(RestoreProductReviewCommand $command): ProductReview
    {
        $review = $this->reviewRepository->findById(ReviewId::fromString($command->reviewId));

        if ($review === null) {
            throw new ReviewNotFound($command->reviewId);
        }

        $review->restore();
        $this->reviewRepository->save($review);

        return $review;
    }
}
