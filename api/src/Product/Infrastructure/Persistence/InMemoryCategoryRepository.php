<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence;

use App\Product\Domain\Entity\Category;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategorySlug;

final class InMemoryCategoryRepository implements CategoryRepository
{
    /** @var array<string, Category> */
    private array $categories = [];

    public function save(Category $category): void
    {
        $this->categories[$category->id()->value()] = $category;
    }

    public function findById(CategoryId $id): ?Category
    {
        return $this->categories[$id->value()] ?? null;
    }

    public function findBySlug(CategorySlug $slug): ?Category
    {
        foreach ($this->categories as $category) {
            if ($category->slug()->value() === $slug->value()) {
                return $category;
            }
        }

        return null;
    }

    public function findByIds(array $ids): array
    {
        $categories = [];

        foreach ($ids as $id) {
            $category = $this->findById($id);

            if ($category !== null) {
                $categories[] = $category;
            }
        }

        return $categories;
    }

    public function findAll(): array
    {
        $categories = array_values($this->categories);
        usort(
            $categories,
            static fn (Category $left, Category $right): int => $left->name()->value() <=> $right->name()->value(),
        );

        return $categories;
    }
}
