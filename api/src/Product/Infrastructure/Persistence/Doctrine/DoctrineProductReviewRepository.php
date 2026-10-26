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
use Doctrine\ORM\QueryBuilder;

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
        return $this->mapRecords($this->productQuery($productId)->getQuery()->getResult());
    }

    public function findPageByProduct(ProductId $productId, int $offset, int $limit): array
    {
        return $this->mapRecords(
            $this->productQuery($productId)
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult(),
        );
    }

    public function countByProduct(ProductId $productId): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(review.id)')
            ->from(ProductReviewRecord::class, 'review')
            ->where('review.productId = :productId')
            ->setParameter('productId', $productId->value())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function averageRatingByProduct(ProductId $productId): ?float
    {
        $average = $this->entityManager->createQueryBuilder()
            ->select('AVG(review.rating)')
            ->from(ProductReviewRecord::class, 'review')
            ->where('review.productId = :productId')
            ->setParameter('productId', $productId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return $average === null ? null : (float) $average;
    }

    private function productQuery(ProductId $productId): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('review')
            ->from(ProductReviewRecord::class, 'review')
            ->where('review.productId = :productId')
            ->setParameter('productId', $productId->value())
            ->orderBy('review.createdAt', 'DESC');
    }

    /**
     * @param list<ProductReviewRecord> $records
     *
     * @return list<ProductReview>
     */
    private function mapRecords(array $records): array
    {
        return array_map(
            static fn (ProductReviewRecord $record): ProductReview => $record->toDomain(),
            $records,
        );
    }
}
