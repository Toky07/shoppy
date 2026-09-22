<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine;

use App\Product\Domain\Entity\Category;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategorySlug;
use App\Product\Infrastructure\Persistence\Doctrine\Entity\CategoryRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCategoryRepository implements CategoryRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Category $category): void
    {
        $record = $this->entityManager->find(CategoryRecord::class, $category->id()->value());

        if ($record === null) {
            $this->entityManager->persist(CategoryRecord::fromDomain($category));
        } else {
            $record->updateFromDomain($category);
        }

        $this->entityManager->flush();
    }

    public function findById(CategoryId $id): ?Category
    {
        return $this->entityManager->find(CategoryRecord::class, $id->value())?->toDomain();
    }

    public function findBySlug(CategorySlug $slug): ?Category
    {
        $record = $this->entityManager->getRepository(CategoryRecord::class)->findOneBy([
            'slug' => $slug->value(),
        ]);

        return $record?->toDomain();
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $values = array_map(static fn (CategoryId $id): string => $id->value(), $ids);
        /** @var list<CategoryRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(CategoryRecord::class, 'category')
            ->where('category.id IN (:ids)')
            ->setParameter('ids', $values)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (CategoryRecord $record): Category => $record->toDomain(),
            $records,
        );
    }

    public function findAll(): array
    {
        /** @var list<CategoryRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(CategoryRecord::class, 'category')
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (CategoryRecord $record): Category => $record->toDomain(),
            $records,
        );
    }
}
