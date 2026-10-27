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
            $this->visibleProductQuery($productId)
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
            ->andWhere('review.hidden = false')
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
            ->andWhere('review.hidden = false')
            ->setParameter('productId', $productId->value())
            ->getQuery()
            ->getSingleScalarResult();

        return $average === null ? null : (float) $average;
    }

    public function findModerationPage(int $offset, int $limit, bool $reportedOnly = false): array
    {
        return $this->mapRecords(
            $this->moderationQuery($reportedOnly)
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult(),
        );
    }

    public function countModeration(bool $reportedOnly = false): int
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('COUNT(review.id)')
            ->from(ProductReviewRecord::class, 'review');

        if ($reportedOnly) {
            $query->andWhere('review.reported = true');
        }

        return (int) $query->getQuery()->getSingleScalarResult();
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

    private function visibleProductQuery(ProductId $productId): QueryBuilder
    {
        return $this->productQuery($productId)->andWhere('review.hidden = false');
    }

    private function moderationQuery(bool $reportedOnly): QueryBuilder
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('review')
            ->from(ProductReviewRecord::class, 'review')
            ->orderBy('review.reported', 'DESC')
            ->addOrderBy('review.createdAt', 'DESC');

        if ($reportedOnly) {
            $query->andWhere('review.reported = true');
        }

        return $query;
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
