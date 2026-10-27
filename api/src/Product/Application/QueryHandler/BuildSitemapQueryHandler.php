<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\CatalogSitemap;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductListCriteria;

final readonly class BuildSitemapQueryHandler
{
    public function __construct(
        private ProductRepository $productRepository,
        private CatalogSitemap $sitemap,
    ) {
    }

    public function handle(): string
    {
        return $this->sitemap->xml(
            $this->productRepository->findPage(0, CatalogSitemap::MAX_PRODUCTS, ProductListCriteria::default()),
        );
    }
}
