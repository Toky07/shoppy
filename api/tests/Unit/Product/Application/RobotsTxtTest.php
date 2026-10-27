<?php

declare(strict_types=1);

use App\Product\Application\RobotsTxt;

it('points crawlers at the storefront sitemap and keeps the console private', function () {
    $robots = (new RobotsTxt('http://localhost:5173/'))->render();

    expect($robots)->toContain("User-agent: *\n")
        ->and($robots)->toContain("Allow: /\n")
        ->and($robots)->toContain("Disallow: /admin\n")
        ->and($robots)->toContain("Disallow: /account\n")
        ->and($robots)->toContain("Disallow: /cart\n")
        ->and($robots)->toContain("Disallow: /orders\n")
        ->and($robots)->toContain("Sitemap: http://localhost:5173/sitemap.xml\n");
});
