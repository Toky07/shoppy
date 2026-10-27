<?php

declare(strict_types=1);

it('exposes a public sitemap of published products', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Seo Tee',
        'priceCents' => 1999,
        'stock' => 3,
        'published' => true,
    ], catalogAdminHeaders());
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Seo Draft',
        'priceCents' => 2999,
        'stock' => 1,
        'published' => false,
    ], catalogAdminHeaders());

    $this->client->request('GET', '/sitemap.xml');
    $response = $this->client->getResponse();
    $xml = (string) $response->getContent();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toContain('application/xml')
        ->and($xml)->toContain('http://localhost:5173/products/seo-tee')
        ->and($xml)->not->toContain('seo-draft');
});

it('exposes robots.txt with the storefront sitemap', function () {
    $this->client->request('GET', '/robots.txt');
    $response = $this->client->getResponse();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toContain('text/plain')
        ->and((string) $response->getContent())->toContain('Sitemap: http://localhost:5173/sitemap.xml');
});
