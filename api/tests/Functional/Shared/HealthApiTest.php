<?php

declare(strict_types=1);

it('reports health without authentication or the catalog', function () {
    $this->client->jsonRequest('GET', '/health');
    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe(['status' => 'ok'])
        ->and($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and((string) $response->getContent())->not->toContain('items');
});
