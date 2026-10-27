<?php

declare(strict_types=1);

use App\Product\Application\CatalogSitemap;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;

it('lists the storefront home and published product pages', function () {
    $products = new InMemoryProductRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440002', 'Draft Hoodie', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));
    $draft = $products->findById(\App\Product\Domain\ValueObject\ProductId::fromString('550e8400-e29b-41d4-a716-446655440002'));
    $draft->changePublication(false);
    $products->save($draft);

    $xml = (new CatalogSitemap('http://localhost:5173/'))->xml($products->findPage(0, 50));

    expect($xml)->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->and($xml)->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
        ->and($xml)->toContain('<loc>http://localhost:5173/</loc>')
        ->and($xml)->toContain('<loc>http://localhost:5173/products/tee</loc>')
        ->and($xml)->toContain('<lastmod>2026-08-19</lastmod>')
        ->and($xml)->not->toContain('draft-hoodie')
        ->and($xml)->not->toContain('Draft');
});
