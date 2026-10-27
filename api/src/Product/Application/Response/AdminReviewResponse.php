<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

use App\Product\Domain\Entity\ProductReview;
use DateTimeInterface;

final readonly class AdminReviewResponse
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $productName,
        public string $productSlug,
        public string $author,
        public int $rating,
        public string $body,
        public string $createdAt,
        public bool $reported,
        public bool $hidden,
    ) {
    }

    public static function fromReview(
        ProductReview $review,
        string $productName,
        string $productSlug,
        string $author,
    ): self {
        return new self(
            $review->id()->value(),
            $review->productId()->value(),
            $productName,
            $productSlug,
            $author,
            $review->rating()->value(),
            $review->body()->value(),
            $review->createdAt()->format(DateTimeInterface::ATOM),
            $review->isReported(),
            $review->isHidden(),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     productId: string,
     *     productName: string,
     *     productSlug: string,
     *     author: string,
     *     rating: int,
     *     body: string,
     *     createdAt: string,
     *     reported: bool,
     *     hidden: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->productId,
            'productName' => $this->productName,
            'productSlug' => $this->productSlug,
            'author' => $this->author,
            'rating' => $this->rating,
            'body' => $this->body,
            'createdAt' => $this->createdAt,
            'reported' => $this->reported,
            'hidden' => $this->hidden,
        ];
    }
}
