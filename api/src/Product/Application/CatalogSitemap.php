<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Entity\Product;
use DateTimeImmutable;

final readonly class CatalogSitemap
{
    public const MAX_PRODUCTS = 5000;

    public function __construct(private string $storefrontBaseUrl)
    {
    }

    /**
     * @param list<Product> $products
     */
    public function xml(array $products): string
    {
        $urls = [$this->url($this->absolute('/'), null, 'daily', '1.0')];

        foreach ($products as $product) {
            if (!$product->isPublished()) {
                continue;
            }

            $urls[] = $this->url(
                $this->absolute('/products/'.$product->slug()->value()),
                $product->createdAt(),
                'weekly',
                '0.8',
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', $urls)
            .'</urlset>';
    }

    private function absolute(string $path): string
    {
        return rtrim($this->storefrontBaseUrl, '/').$path;
    }

    private function url(string $loc, ?DateTimeImmutable $lastmod, string $changefreq, string $priority): string
    {
        $xml = '<url><loc>'.$this->escape($loc).'</loc>';

        if ($lastmod !== null) {
            $xml .= '<lastmod>'.$lastmod->format('Y-m-d').'</lastmod>';
        }

        return $xml.'<changefreq>'.$changefreq.'</changefreq><priority>'.$priority.'</priority></url>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
