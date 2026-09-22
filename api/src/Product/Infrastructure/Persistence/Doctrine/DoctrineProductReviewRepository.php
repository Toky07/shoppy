<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine;

use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Repository\ProductReviewRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewId;
use App\Product\Infrastructure\Persistence\Doctrine\Entity\ProductReviewRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineProductReviewRepository implements ProductReviewRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(ProductReview $review): void
    {
        $record = $this->entityManager->find(ProductReviewRecord::class, $review->id()->value());

        if ($record === null) {
            $this->entityManager->persist(ProductReviewRecord::fromDomain($review));
        } else {
            $record->updateFromDomain($review);
        }

        $this->entityManager->flush();
    }

    public function findById(ReviewId $id): ?ProductReview
    {
        return $this->entityManager->find(ProductReviewRecord::class, $id->value())?->toDomain();
    }

    public function findByProductAndAuthor(ProductId $productId, ReviewAuthorId $authorId): ?ProductReview
    {
        $record = $this->entityManager->getRepository(ProductReviewRecord::class)->findOneBy([
            'productId' => $productId->value(),
            'authorId' => $authorId->value(),
        ]);

        return $record?->toDomain();
    }

    public function findByProduct(ProductId $productId): array
    {
        /** @var list<ProductReviewRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('review')
            ->from(ProductReviewRecord::class, 'review')
            ->where('review.productId = :productId')
            ->setParameter('productId', $productId->value())
            ->orderBy('review.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (ProductReviewRecord $record): ProductReview => $record->toDomain(),
            $records,
        );
    }
}
