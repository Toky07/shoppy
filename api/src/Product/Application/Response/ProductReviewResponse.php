<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

use App\Product\Domain\Entity\ProductReview;
use DateTimeInterface;

final readonly class ProductReviewResponse
{
    public function __construct(
        public string $id,
        public int $rating,
        public string $body,
        public string $author,
        public string $createdAt,
        public bool $mine,
    ) {
    }

    public static function fromReview(ProductReview $review, string $author, bool $mine): self
    {
        return new self(
            $review->id()->value(),
            $review->rating()->value(),
            $review->body()->value(),
            $author,
            $review->createdAt()->format(DateTimeInterface::ATOM),
            $mine,
        );
    }

    /**
     * @return array{id: string, rating: int, body: string, author: string, createdAt: string, mine: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'body' => $this->body,
            'author' => $this->author,
            'createdAt' => $this->createdAt,
            'mine' => $this->mine,
        ];
    }
}
