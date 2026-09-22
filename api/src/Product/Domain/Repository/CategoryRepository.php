<?php

declare(strict_types=1);

namespace App\Product\Domain\Repository;

use App\Product\Domain\Entity\Category;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategorySlug;

interface CategoryRepository
{
    public function save(Category $category): void;

    public function findById(CategoryId $id): ?Category;

    public function findBySlug(CategorySlug $slug): ?Category;

    /**
     * @param list<CategoryId> $ids
     *
     * @return list<Category>
     */
    public function findByIds(array $ids): array;

    /**
     * @return list<Category>
     */
    public function findAll(): array;
}
