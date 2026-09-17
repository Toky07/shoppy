<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Domain\ValueObject\ProductSort;
use App\Product\Infrastructure\Persistence\Doctrine\Entity\ProductRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final readonly class DoctrineProductRepository implements ProductRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Product $product): void
    {
        $record = $this->entityManager->find(ProductRecord::class, $product->id()->value());

        if ($record === null) {
            $this->entityManager->persist(ProductRecord::fromDomain($product));
        } else {
            $record->updateFromDomain($product);
        }

        $this->entityManager->flush();
    }

    public function findById(ProductId $id): ?Product
    {
        $record = $this->entityManager->find(ProductRecord::class, $id->value());

        return $record?->toDomain();
    }

    public function findByName(ProductName $name): ?Product
    {
        $record = $this->entityManager->getRepository(ProductRecord::class)->findOneBy([
            'name' => $name->value(),
        ]);

        return $record?->toDomain();
    }

    public function findBySlug(ProductSlug $slug): ?Product
    {
        $record = $this->entityManager->getRepository(ProductRecord::class)->findOneBy([
            'slug' => $slug->value(),
        ]);

        return $record?->toDomain();
    }

    public function findPage(int $offset, int $limit, ?ProductListCriteria $criteria = null): array
    {
        $criteria ??= ProductListCriteria::default();
        $queryBuilder = $this->createListQueryBuilder($criteria)
            ->select('product')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $this->applySort($queryBuilder, $criteria->sort);

        $records = $queryBuilder->getQuery()->getResult();

        return array_map(
            static fn (ProductRecord $record): Product => $record->toDomain(),
            $records,
        );
    }

    public function countAll(?ProductListCriteria $criteria = null): int
    {
        $criteria ??= ProductListCriteria::default();

        return (int) $this->createListQueryBuilder($criteria)
            ->select('COUNT(product.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createListQueryBuilder(ProductListCriteria $criteria): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(ProductRecord::class, 'product');

        if ($criteria->search !== null) {
            $queryBuilder
                ->andWhere('LOWER(product.name) LIKE :search OR LOWER(COALESCE(product.description, \'\')) LIKE :search')
                ->setParameter('search', $this->toLikePattern($criteria->search));
        }

        return $queryBuilder;
    }

    private function applySort(QueryBuilder $queryBuilder, ProductSort $sort): void
    {
        match ($sort->value()) {
            ProductSort::OLDEST => $queryBuilder
                ->orderBy('product.createdAt', 'ASC')
                ->addOrderBy('product.name', 'ASC'),
            ProductSort::PRICE_ASC => $queryBuilder
                ->orderBy('product.priceCents', 'ASC')
                ->addOrderBy('product.name', 'ASC'),
            ProductSort::PRICE_DESC => $queryBuilder
                ->orderBy('product.priceCents', 'DESC')
                ->addOrderBy('product.name', 'ASC'),
            ProductSort::NAME_ASC => $queryBuilder->orderBy('product.name', 'ASC'),
            default => $queryBuilder
                ->orderBy('product.createdAt', 'DESC')
                ->addOrderBy('product.name', 'ASC'),
        };
    }

    private function toLikePattern(string $search): string
    {
        $needle = str_replace(['\\', '%', '_'], '', strtolower($search));

        return '%'.$needle.'%';
    }

    public function delete(Product $product): void
    {
        $record = $this->entityManager->find(ProductRecord::class, $product->id()->value());

        if ($record === null) {
            return;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }
}
