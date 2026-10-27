<?php

declare(strict_types=1);

use App\Product\Application\CatalogSitemap;
use App\Product\Application\QueryHandler\BuildSitemapQueryHandler;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('builds a sitemap from published products only', function () {
    $products = new InMemoryProductRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440002', 'Hidden Hoodie', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));
    $draft = $products->findById(ProductId::fromString('550e8400-e29b-41d4-a716-446655440002'));
    $draft->changePublication(false);
    $products->save($draft);

    $xml = (new BuildSitemapQueryHandler($products, new CatalogSitemap('http://localhost:5173')))->handle();

    expect($xml)->toContain('/products/tee')
        ->and($xml)->not->toContain('hidden-hoodie');
});
