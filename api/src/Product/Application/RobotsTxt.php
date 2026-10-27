<?php

declare(strict_types=1);

namespace App\Product\Application;

final readonly class RobotsTxt
{
    public function __construct(private string $storefrontBaseUrl)
    {
    }

    public function render(): string
    {
        $sitemap = rtrim($this->storefrontBaseUrl, '/').'/sitemap.xml';

        return "User-agent: *\n"
            ."Allow: /\n"
            ."Disallow: /admin\n"
            ."Disallow: /account\n"
            ."Disallow: /cart\n"
            ."Disallow: /orders\n"
            ."Disallow: /login\n"
            ."Disallow: /register\n"
            ."\n"
            .'Sitemap: '.$sitemap."\n";
    }
}
