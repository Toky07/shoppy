<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\Response\CategoryResponse;
use App\Product\Domain\Entity\Category;
use App\Product\Domain\Repository\CategoryRepository;

final readonly class ListCategoriesQueryHandler
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    /**
     * @return list<CategoryResponse>
     */
    public function handle(): array
    {
        return array_map(
            static fn (Category $category): CategoryResponse => CategoryResponse::fromCategory($category),
            $this->categoryRepository->findAll(),
        );
    }
}
