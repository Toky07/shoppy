<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductSlug;

final readonly class UniqueProductSlug
{
    public function __construct(private ProductRepository $products)
    {
    }

    public function allocate(ProductName $name, ?ProductId $except = null): ProductSlug
    {
        $base = ProductSlug::fromName($name);
        $candidate = $base;
        $copy = 2;

        while ($this->isTaken($candidate, $except)) {
            $candidate = $base->withCopyNumber($copy);
            ++$copy;
        }

        return $candidate;
    }

    private function isTaken(ProductSlug $slug, ?ProductId $except): bool
    {
        $existing = $this->products->findBySlug($slug);

        if ($existing === null) {
            return false;
        }

        return $except === null || $existing->id()->value() !== $except->value();
    }
}
