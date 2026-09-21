<?php

declare(strict_types=1);

it('lists users for an admin', function () {
    registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('GET', '/admin/users', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(1)
        ->and($payload['total'])->toBeGreaterThanOrEqual(2)
        ->and(array_column($payload['items'], 'email'))->toContain('ada@nuvora.test')
        ->and($payload['items'][0])->not->toHaveKey('password');
});

it('filters listed users by email', function () {
    registerCatalogUser($this->client, 'ada@nuvora.test');
    registerCatalogUser($this->client, 'bob@shoppy.test');

    $this->client->jsonRequest('GET', '/admin/users?q=ada', [], catalogAdminHeaders());

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['total'])->toBe(1)
        ->and($payload['items'][0]['email'])->toBe('ada@nuvora.test');
});

it('rejects unauthenticated user listing', function () {
    $this->client->jsonRequest('GET', '/admin/users');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('rejects a customer listing users', function () {
    $this->client->jsonRequest('GET', '/admin/users', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});
