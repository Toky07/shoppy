<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategoryName;
use App\Product\Domain\ValueObject\CategorySlug;

final class Category
{
    private function __construct(
        private CategoryId $id,
        private CategoryName $name,
        private CategorySlug $slug,
    ) {
    }

    public static function create(CategoryId $id, CategoryName $name, ?CategorySlug $slug = null): self
    {
        return new self($id, $name, $slug ?? CategorySlug::fromName($name));
    }

    public function id(): CategoryId
    {
        return $this->id;
    }

    public function name(): CategoryName
    {
        return $this->name;
    }

    public function slug(): CategorySlug
    {
        return $this->slug;
    }
}
