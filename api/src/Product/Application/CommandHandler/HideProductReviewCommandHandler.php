<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\HideProductReviewCommand;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\ReviewNotFound;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ReviewId;

final readonly class HideProductReviewCommandHandler
{
    public function __construct(private ProductReviewRepository $reviewRepository)
    {
    }

    public function handle(HideProductReviewCommand $command): ProductReview
    {
        $review = $this->reviewRepository->findById(ReviewId::fromString($command->reviewId));

        if ($review === null) {
            throw new ReviewNotFound($command->reviewId);
        }

        $review->hide();
        $this->reviewRepository->save($review);

        return $review;
    }
}
