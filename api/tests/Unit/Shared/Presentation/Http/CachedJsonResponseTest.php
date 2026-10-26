<?php

declare(strict_types=1);

use App\Shared\Presentation\Http\CachedJsonResponse;
use Symfony\Component\HttpFoundation\Request;

it('marks a json payload as a short public cache with an etag', function () {
    $response = CachedJsonResponse::public(Request::create('/products'), [
        'name' => 'Nuvora Tee',
    ]);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('ETag'))->not->toBeEmpty()
        ->and($response->headers->get('Cache-Control'))->toContain('public')
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=30')
        ->and($response->headers->get('Cache-Control'))->toContain('must-revalidate')
        ->and(json_decode((string) $response->getContent(), true))->toBe(['name' => 'Nuvora Tee']);
});

it('returns not modified when the etag still matches', function () {
    $fresh = CachedJsonResponse::public(Request::create('/products'), ['name' => 'Nuvora Tee']);
    $etag = $fresh->headers->get('ETag');
    $request = Request::create('/products');
    $request->headers->set('If-None-Match', $etag);

    $response = CachedJsonResponse::public($request, ['name' => 'Nuvora Tee']);

    expect($response->getStatusCode())->toBe(304)
        ->and($response->getContent())->toBe('');
});
