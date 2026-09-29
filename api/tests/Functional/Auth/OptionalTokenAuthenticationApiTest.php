<?php

declare(strict_types=1);

it('treats an invalid bearer token as anonymous on a public route', function () {
    $this->client->jsonRequest('GET', '/products', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat('b', 64),
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toHaveKeys(['items', 'page', 'limit', 'total'])
        ->and((string) $response->getContent())->not->toContain(str_repeat('b', 64));
});

it('rejects an invalid bearer token on a protected route', function () {
    $this->client->jsonRequest('GET', '/cart', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat('c', 64),
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
