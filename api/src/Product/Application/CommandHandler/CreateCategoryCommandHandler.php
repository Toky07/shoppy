<?php

declare(strict_types=1);

namespace App\Product\Application\CommandHandler;

use App\Product\Application\Command\CreateCategoryCommand;
use App\Product\Application\UniqueCategorySlug;
use App\Product\Domain\Entity\Category;
use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategoryName;

final readonly class CreateCategoryCommandHandler
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private UniqueCategorySlug $uniqueCategorySlug,
    ) {
    }

    public function handle(CreateCategoryCommand $command): Category
    {
        $name = CategoryName::fromString($command->name);
        $category = Category::create(
            CategoryId::generate(),
            $name,
            $this->uniqueCategorySlug->allocate($name),
        );
        $this->categoryRepository->save($category);

        return $category;
    }
}
