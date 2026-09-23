<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSku;
use App\Product\Domain\ValueObject\ProductSlug;
use App\Product\Infrastructure\Persistence\Doctrine\Entity\ProductVariantRecord;
use App\Product\Domain\ValueObject\ProductSort;
use App\Product\Infrastructure\Persistence\Doctrine\Entity\ProductRecord;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\SQLitePlatform;
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

    public function findByIdForUpdate(ProductId $id): ?Product
    {
        if ($this->entityManager->getConnection()->getDatabasePlatform() instanceof SQLitePlatform) {
            return $this->findById($id);
        }

        $record = $this->entityManager->find(
            ProductRecord::class,
            $id->value(),
            LockMode::PESSIMISTIC_WRITE,
        );

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

    public function findBySku(ProductSku $sku): ?Product
    {
        $record = $this->entityManager->getRepository(ProductRecord::class)->findOneBy([
            'sku' => $sku->value(),
        ]);

        if ($record !== null) {
            return $record->toDomain();
        }

        $variant = $this->entityManager->getRepository(ProductVariantRecord::class)->findOneBy([
            'sku' => $sku->value(),
        ]);

        return $variant?->product()->toDomain();
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $values = array_map(static fn (ProductId $id): string => $id->value(), $ids);
        /** @var list<ProductRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(ProductRecord::class, 'product')
            ->where('product.id IN (:ids)')
            ->setParameter('ids', $values)
            ->getQuery()
            ->getResult();

        $indexed = [];

        foreach ($records as $record) {
            $product = $record->toDomain();
            $indexed[$product->id()->value()] = $product;
        }

        $ordered = [];

        foreach ($values as $value) {
            if (isset($indexed[$value])) {
                $ordered[] = $indexed[$value];
            }
        }

        return $ordered;
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

        if ($criteria->minPriceCents !== null) {
            $queryBuilder
                ->andWhere('product.priceCents >= :minPrice')
                ->setParameter('minPrice', $criteria->minPriceCents);
        }

        if ($criteria->maxPriceCents !== null) {
            $queryBuilder
                ->andWhere('product.priceCents <= :maxPrice')
                ->setParameter('maxPrice', $criteria->maxPriceCents);
        }

        if ($criteria->inStockOnly) {
            $queryBuilder->andWhere('product.stock > 0');
        }

        if ($criteria->categoryId !== null) {
            $queryBuilder
                ->andWhere('product.categoryId = :categoryId')
                ->setParameter('categoryId', $criteria->categoryId->value());
        }

        if ($criteria->publishedOnly) {
            $queryBuilder->andWhere('product.published = true');
        }

        return $queryBuilder;
    }

    public function findRelated(Product $product, int $limit): array
    {
        $categoryId = $product->categoryId();

        if ($categoryId === null || $limit < 1) {
            return [];
        }

        /** @var list<ProductRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(ProductRecord::class, 'product')
            ->where('product.categoryId = :categoryId')
            ->andWhere('product.id != :id')
            ->andWhere('product.published = true')
            ->setParameter('categoryId', $categoryId->value())
            ->setParameter('id', $product->id()->value())
            ->orderBy('product.createdAt', 'DESC')
            ->addOrderBy('product.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (ProductRecord $record): Product => $record->toDomain(),
            $records,
        );
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
