<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use DateTimeImmutable;

final class ProductReview
{
    private function __construct(
        private ReviewId $id,
        private ProductId $productId,
        private ReviewAuthorId $authorId,
        private Rating $rating,
        private ReviewBody $body,
        private DateTimeImmutable $createdAt,
        private bool $hidden,
        private bool $reported,
    ) {
    }

    public static function submit(
        ReviewId $id,
        ProductId $productId,
        ReviewAuthorId $authorId,
        Rating $rating,
        ReviewBody $body,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $productId, $authorId, $rating, $body, $createdAt, false, false);
    }

    public static function reconstitute(
        ReviewId $id,
        ProductId $productId,
        ReviewAuthorId $authorId,
        Rating $rating,
        ReviewBody $body,
        DateTimeImmutable $createdAt,
        bool $hidden,
        bool $reported,
    ): self {
        return new self($id, $productId, $authorId, $rating, $body, $createdAt, $hidden, $reported);
    }

    public function id(): ReviewId
    {
        return $this->id;
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function authorId(): ReviewAuthorId
    {
        return $this->authorId;
    }

    public function rating(): Rating
    {
        return $this->rating;
    }

    public function body(): ReviewBody
    {
        return $this->body;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function isReported(): bool
    {
        return $this->reported;
    }

    public function revise(Rating $rating, ReviewBody $body): void
    {
        $this->rating = $rating;
        $this->body = $body;
    }

    public function report(): void
    {
        $this->reported = true;
    }

    public function hide(): void
    {
        $this->hidden = true;
    }

    public function restore(): void
    {
        $this->hidden = false;
    }
}
