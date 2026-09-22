<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

use App\Product\Domain\Entity\Category;

final readonly class CategoryResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
    ) {
    }

    public static function fromCategory(Category $category): self
    {
        return new self(
            $category->id()->value(),
            $category->name()->value(),
            $category->slug()->value(),
        );
    }

    /**
     * @return array{id: string, name: string, slug: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
