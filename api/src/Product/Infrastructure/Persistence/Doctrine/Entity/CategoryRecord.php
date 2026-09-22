<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Persistence\Doctrine\Entity;

use App\Product\Domain\Entity\Category;
use App\Product\Domain\ValueObject\CategoryId;
use App\Product\Domain\ValueObject\CategoryName;
use App\Product\Domain\ValueObject\CategorySlug;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'categories')]
#[ORM\UniqueConstraint(name: 'uniq_categories_slug', columns: ['slug'])]
class CategoryRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $slug;

    public static function fromDomain(Category $category): self
    {
        $record = new self();
        $record->id = $category->id()->value();
        $record->syncFromDomain($category);

        return $record;
    }

    public function updateFromDomain(Category $category): void
    {
        $this->syncFromDomain($category);
    }

    public function toDomain(): Category
    {
        return Category::create(
            CategoryId::fromString($this->id),
            CategoryName::fromString($this->name),
            CategorySlug::fromString($this->slug),
        );
    }

    private function syncFromDomain(Category $category): void
    {
        $this->name = $category->name()->value();
        $this->slug = $category->slug()->value();
    }
}
