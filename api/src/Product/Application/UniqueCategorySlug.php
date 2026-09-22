<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Repository\CategoryRepository;
use App\Product\Domain\ValueObject\CategoryName;
use App\Product\Domain\ValueObject\CategorySlug;

final readonly class UniqueCategorySlug
{
    public function __construct(private CategoryRepository $categories)
    {
    }

    public function allocate(CategoryName $name): CategorySlug
    {
        $base = CategorySlug::fromName($name);
        $candidate = $base;
        $copy = 2;

        while ($this->categories->findBySlug($candidate) !== null) {
            $candidate = $base->withCopyNumber($copy);
            ++$copy;
        }

        return $candidate;
    }
}
