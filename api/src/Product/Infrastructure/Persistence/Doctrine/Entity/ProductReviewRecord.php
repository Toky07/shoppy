<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_reviews')]
#[ORM\UniqueConstraint(name: 'uniq_product_reviews_author', columns: ['product_id', 'author_id'])]
#[ORM\Index(name: 'idx_product_reviews_product_id', columns: ['product_id'])]
class ProductReviewRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(name: 'product_id', length: 36)]
    private string $productId;

    #[ORM\Column(name: 'author_id', length: 36)]
    private string $authorId;

    #[ORM\Column]
    private int $rating;

    #[ORM\Column(type: Types::TEXT)]
    private string $body;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(ProductReview $review): self
    {
        $record = new self();
        $record->id = $review->id()->value();
        $record->productId = $review->productId()->value();
        $record->authorId = $review->authorId()->value();
        $record->createdAt = $review->createdAt();
        $record->sync($review);

        return $record;
    }

    public function updateFromDomain(ProductReview $review): void
    {
        $this->sync($review);
    }

    public function toDomain(): ProductReview
    {
        return ProductReview::submit(
            ReviewId::fromString($this->id),
            ProductId::fromString($this->productId),
            ReviewAuthorId::fromString($this->authorId),
            Rating::fromInt($this->rating),
            ReviewBody::fromString($this->body),
            $this->createdAt,
        );
    }

    private function sync(ProductReview $review): void
    {
        $this->rating = $review->rating()->value();
        $this->body = $review->body()->value();
    }
}
